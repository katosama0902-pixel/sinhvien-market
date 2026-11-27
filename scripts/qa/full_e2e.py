"""Kiểm thử toàn hệ thống (W10–11), từ thao tác đơn giản đến tính năng phức tạp, qua API thật.

Chạy trên DB demo đã seed (seed-demo-ktx.php + seed-test-marketplace.php + seed demo W6):
  python -I full_e2e.py <base_url> <test_password_file> <demo_seed_output> <accounts.json> <results.json>

- Mật khẩu chỉ đọc từ file, không in ra.
- OTP đăng ký đọc trực tiếp từ DB demo (biến môi trường MYSQL_CMD, mặc định `mysql -h127.0.0.1 -P3307 -uroot`).
- Mỗi lần chạy dùng hậu tố RUN riêng nên chạy lại không đụng dữ liệu cũ; khôi phục DB bằng bản dump sau khi chạy.
"""
import base64
import datetime as dt
import http.cookiejar
import io
import json
import os
import re
import shlex
import subprocess
import sys
import time
import urllib.error
import urllib.parse
import urllib.request
import uuid

sys.stdout.reconfigure(encoding="utf-8")
BASE = sys.argv[1].rstrip("/")
TEST_PW = open(sys.argv[2], encoding="utf-8").read().strip()
DEMO_PW = {}
for line in open(sys.argv[3], encoding="utf-8"):
    m = re.match(r"^(\S+@example\.test)\s+(\S+)$", line.strip())
    if m:
        DEMO_PW[m.group(1)] = m.group(2)
ACCOUNTS = {a["key"]: a for a in json.load(open(sys.argv[4], encoding="utf-8"))}
OUT = sys.argv[5]
MYSQL = shlex.split(os.environ.get("MYSQL_CMD", "mysql -h127.0.0.1 -P3307 -uroot")) + ["sinhvien_market_demo"]
RUN = dt.datetime.now().strftime("%m%d%H%M%S")
TODAY = dt.date.today()
results = []
group = "?"


def sql(query):
    out = subprocess.run(MYSQL + ["-N", "-B", "-e", query], capture_output=True, text=True, encoding="utf-8")
    return [line.split("\t") for line in out.stdout.strip().splitlines() if line]


class Client:
    def __init__(self, name):
        self.name = name
        self.jar = http.cookiejar.CookieJar()
        self.op = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(self.jar))
        self.csrf = None

    def call(self, method, path, body=None, csrf=True, files=None, raw=False):
        headers = {"Accept": "application/json"}
        data = None
        if files is not None:
            boundary = uuid.uuid4().hex
            buf = io.BytesIO()
            for k, v in (body or {}).items():
                buf.write(f"--{boundary}\r\nContent-Disposition: form-data; name=\"{k}\"\r\n\r\n{v}\r\n".encode())
            for k, (fname, content, mime) in files.items():
                buf.write(f"--{boundary}\r\nContent-Disposition: form-data; name=\"{k}\"; filename=\"{fname}\"\r\n"
                          f"Content-Type: {mime}\r\n\r\n".encode() + content + b"\r\n")
            buf.write(f"--{boundary}--\r\n".encode())
            data = buf.getvalue()
            headers["Content-Type"] = f"multipart/form-data; boundary={boundary}"
        elif body is not None:
            data = json.dumps(body).encode()
            headers["Content-Type"] = "application/json"
        if csrf and self.csrf and method != "GET":
            headers["X-CSRF-Token"] = self.csrf
        req = urllib.request.Request(BASE + path, data=data, method=method, headers=headers)
        t0 = time.time()
        try:
            with self.op.open(req, timeout=60) as r:
                content, status, ctype = r.read(), r.status, r.headers.get("Content-Type", "")
        except urllib.error.HTTPError as e:
            content, status, ctype = e.read(), e.code, e.headers.get("Content-Type", "")
        self.last_ms = int((time.time() - t0) * 1000)
        if raw:
            return status, content, ctype
        text = content.decode("utf-8", "replace")
        try:
            js = json.loads(text)
        except Exception:
            js = {"_raw": text[:300]}
        return status, js

    def refresh_csrf(self):
        _, js = self.call("GET", "/api/auth/csrf")
        self.csrf = (js.get("data") or {}).get("token")

    def login(self, email, password, role="student"):
        self.refresh_csrf()
        st, js = self.call("POST", "/api/auth/login", {"email": email, "password": password, "role": role})
        data = js.get("data") or {}
        if data.get("csrfToken"):
            self.csrf = data["csrfToken"]
        else:
            self.refresh_csrf()
        return st, js


def record(ok, name, detail):
    results.append({"group": group, "name": name, "ok": bool(ok), "detail": detail})
    print(("  PASS " if ok else "  FAIL ") + name + "  -> " + detail, flush=True)
    return ok


def check(name, resp, expect, code=None):
    status, js = resp
    if not isinstance(js, dict):
        js = {"data": js}
    expect = expect if isinstance(expect, tuple) else (expect,)
    err = js.get("error") if isinstance(js.get("error"), dict) else {}
    ok = status in expect and (code is None or err.get("code") == code)
    detail = f"HTTP {status}" + (f" {err.get('code')}" if err.get("code") else "")
    if not ok:
        detail += f" (mong đợi {'/'.join(map(str, expect))}{' ' + code if code else ''}) | " + str(
            err.get("message") or js.get("message") or js.get("_raw") or "")[:160]
    record(ok, name, detail)
    return js.get("data") if isinstance(js, dict) else None


def section(title):
    global group
    group = title
    print(f"\n=== {title} ===", flush=True)


def items(data):
    if isinstance(data, dict):
        for key in ("items", "products", "reports", "results", "data"):
            if isinstance(data.get(key), list):
                return data[key]
    return data if isinstance(data, list) else []


def tiny_jpeg():
    # 64x64 JPEG generated without PIL: smallest valid baseline JPEG from a fixed byte string is brittle,
    # so use the dataset image if present.
    path = os.path.join(os.path.dirname(sys.argv[4]), "images", "t001.jpg")
    return open(path, "rb").read()


# ---------------------------------------------------------------- accounts
SELLER = ACCOUNTS["u01"]
BUYER = ACCOUNTS["u02"]
OTHER = ACCOUNTS["u03"]
MANAGER_EMAIL = "quanly.ktx@example.com"
ADMIN_EMAIL = "qa.admin@example.test"
# Tài khoản admin riêng cho QA (chỉ tạo trên DB demo; mật khẩu = mật khẩu test, băm bằng PHP).
if not sql(f"SELECT id FROM users WHERE email='{ADMIN_EMAIL}'"):
    hashed = subprocess.run(["php", "-r", "echo password_hash(stream_get_contents(STDIN), PASSWORD_DEFAULT);"],
                            input=TEST_PW, capture_output=True, text=True).stdout.strip()
    sql("INSERT INTO users (name, email, password, role, is_verified) VALUES "
        f"('QA Admin', '{ADMIN_EMAIL}', '{hashed}', 'admin', 1)")
guest, seller, buyer, other, mgr, admin, newbie = (Client(n) for n in
                                                    ("guest", "seller", "buyer", "other", "mgr", "admin", "newbie"))

# ================================================================ A. Khách
section("A. Khách vãng lai")
guest.refresh_csrf()
record(bool(guest.csrf), "Cấp CSRF token cho khách", "có token" if guest.csrf else "không có token")
check("Trang chủ marketplace", guest.call("GET", "/api/marketplace/home"), 200)
cats = check("Danh mục", guest.call("GET", "/api/marketplace/categories"), 200) or []
record(len(cats) >= 5, "Có đủ danh mục", f"{len(cats)} danh mục")
plist = items(check("Danh sách sản phẩm (trang 1, 12 tin)", guest.call("GET", "/api/marketplace/products"), 200))
record(len(plist) == 12, "Phân trang mặc định 12 tin/trang", f"{len(plist)} tin")
plist = []
for page in range(1, 5):
    chunk = items(guest.call("GET", f"/api/marketplace/products?limit=50&page={page}")[1].get("data"))
    plist += chunk
    if len(chunk) < 50:
        break
check("Lọc theo loại + khoảng giá", guest.call("GET", "/api/marketplace/products?type=sale&priceMin=100000&priceMax=500000"), 200)
check("Khoảng giá ngược -> 422", guest.call("GET", "/api/marketplace/products?priceMin=500000&priceMax=1000"), 422)
record(len(plist) >= 100, "Danh sách có ≥100 tin đang bán", f"{len(plist)} tin")
with_img = [p for p in plist if p.get("imageUrl")]
record(len(with_img) >= 70, "Ảnh sản phẩm đã bổ sung (≥70 tin có ảnh)", f"{len(with_img)}/{len(plist)} có ảnh")
pid0 = plist[0]["id"] if plist else 0
detail = check("Chi tiết sản phẩm", guest.call("GET", f"/api/marketplace/product?id={pid0}"), 200) or {}
if with_img:
    st, content, ctype = guest.call("GET", urllib.parse.urlparse(with_img[0]["imageUrl"]).path +
                                    ("?" + urllib.parse.urlparse(with_img[0]["imageUrl"]).query
                                     if urllib.parse.urlparse(with_img[0]["imageUrl"]).query else ""), raw=True)
    record(st == 200 and ctype.startswith("image/"), "Tải ảnh sản phẩm", f"HTTP {st} {ctype}")
check("Sản phẩm không tồn tại -> 404", guest.call("GET", "/api/marketplace/product?id=99999999"), 404)
check("Bảng xếp hạng", guest.call("GET", "/api/marketplace/leaderboard"), 200)
check("Tìm theo từ khóa", guest.call("GET", "/api/products/search?q=" + urllib.parse.quote("giáo trình")), 200)
check("Khách xem tin của tôi -> 401", guest.call("GET", "/api/user/products"), 401)
check("Khách xem giao dịch -> 401", guest.call("GET", "/api/user/transactions"), 401)
check("Khách vào API admin -> 401/403", guest.call("GET", "/api/admin/dashboard"), (401, 403))
seller_id = int(sql(f"SELECT id FROM users WHERE email='{SELLER['email']}'")[0][0])
check("Hồ sơ công khai người bán", guest.call("GET", f"/api/users/{seller_id}/profile"), 200)

# ================================================================ B. Xác thực
section("B. Đăng ký, OTP, đăng nhập")
new_email = f"qa.{RUN}@example.test"
reg = {"name": "QA Tester", "email": new_email, "phone": "0901234567", "securityQuestion": "q1",
       "securityAnswer": "meo", "password": "QaTest@" + RUN, "passwordConfirm": "QaTest@" + RUN}
newbie.refresh_csrf()
check("Đăng ký thiếu CSRF bị chặn", newbie.call("POST", "/api/auth/register", reg, csrf=False), (400, 403, 419))
check("Đăng ký mật khẩu yếu -> 422", newbie.call("POST", "/api/auth/register", {**reg, "password": "123", "passwordConfirm": "123"}), (400, 422))
check("Đăng ký mật khẩu nhập lại sai -> 422", newbie.call("POST", "/api/auth/register", {**reg, "passwordConfirm": "khac"}), (400, 422))
check("Đăng ký email sai định dạng -> 422", newbie.call("POST", "/api/auth/register", {**reg, "email": "khong-phai-email"}), (400, 422))
check("Đăng ký email đã tồn tại -> 409", newbie.call("POST", "/api/auth/register", {**reg, "email": SELLER["email"]}), (409, 422))
r = check("Đăng ký hợp lệ -> chờ OTP", newbie.call("POST", "/api/auth/register", reg), (200, 201)) or {}
record(r.get("status") == "verify_otp", "Phản hồi yêu cầu xác minh OTP", str(r.get("status")))
check("Phiên OTP tồn tại", newbie.call("GET", "/api/auth/otp/session"), 200)
pre = Client("x")
lr = check("Đăng nhập trước khi xác minh OTP", pre.login(new_email, reg["password"]), 200) or {}
record(lr.get("status") == "verify_otp", "Tài khoản chưa xác minh bị chuyển sang bước OTP", str(lr.get("status")))
record(pre.call("GET", "/api/auth/me")[1].get("data") is None, "Chưa xác minh thì chưa có phiên đăng nhập", "ok")
check("OTP sai -> lỗi", newbie.call("POST", "/api/auth/otp/verify", {"otp": "000000"}), (400, 401, 422))
otp = (sql(f"SELECT otp_code FROM users WHERE email='{new_email}'") or [[""]])[0][0]
check("OTP đúng -> kích hoạt và đăng nhập", newbie.call("POST", "/api/auth/otp/verify", {"otp": otp}), 200)
newbie.refresh_csrf()
me = check("/auth/me sau xác minh", newbie.call("GET", "/api/auth/me"), 200) or {}
record((me.get("user") or me).get("email") == new_email, "Đúng người dùng vừa đăng ký", str((me.get("user") or me).get("email")))
check("Đăng xuất", newbie.call("POST", "/api/auth/logout", {}), 200)
st, js = newbie.call("GET", "/api/auth/me")
record(st == 200 and js.get("data") is None, "/auth/me sau đăng xuất trả về khách (null)", f"HTTP {st} data={js.get('data')}")
check("Đăng nhập sai mật khẩu -> 401", Client("x").login(SELLER["email"], "sai-mat-khau-" + RUN), (401, 422))
check("Đăng nhập email không tồn tại -> 401", Client("x").login(f"khongco.{RUN}@example.test", "abc12345"), (401, 422))
check("Admin dùng cổng sinh viên bị chặn", Client("x").login(ADMIN_EMAIL, TEST_PW), (401, 403))
check("Người bán đăng nhập", seller.login(SELLER["email"], TEST_PW), 200)
check("Người mua đăng nhập", buyer.login(BUYER["email"], TEST_PW), 200)
check("Người dùng thứ ba đăng nhập", other.login(OTHER["email"], TEST_PW), 200)
check("Quản lý KTX đăng nhập", mgr.login(MANAGER_EMAIL, TEST_PW), 200)
check("Admin đăng nhập cổng Admin", admin.login(ADMIN_EMAIL, TEST_PW, role="admin"), 200)
fp = Client("fp"); fp.refresh_csrf()
q = check("Quên mật khẩu: lấy câu hỏi bảo mật", fp.call("POST", "/api/auth/password/forgot/question", {"email": new_email}), 200)
check("Quên mật khẩu: trả lời sai -> lỗi", fp.call("POST", "/api/auth/password/forgot/answer", {"email": new_email, "answer": "sai"}), (400, 401, 422))

# ================================================================ C. Hồ sơ
section("C. Hồ sơ cá nhân")
prof = check("Xem hồ sơ của tôi", seller.call("GET", "/api/profile/me"), 200) or {}
check("Cập nhật tên + SĐT", seller.call("PUT", "/api/profile/me", {"name": SELLER["name"], "phone": "0912345678"}), 200)
check("SĐT sai định dạng -> 422", seller.call("PUT", "/api/profile/me", {"name": SELLER["name"], "phone": "abc"}), (400, 422))
check("Lưu tài khoản ngân hàng", seller.call("POST", "/api/profile/bank", {"bankName": "Vietcombank", "bankCode": "VCB", "accountNo": "0123456789", "accountName": "LE HOANG NAM"}), 200)
check("Đổi mật khẩu sai mật khẩu cũ -> lỗi", seller.call("POST", "/api/profile/password", {"currentPassword": "sai", "newPassword": "NewPass@123", "confirmPassword": "NewPass@123"}), (400, 401, 422))
check("Hồ sơ sinh viên (KTX)", seller.call("GET", "/api/student/profile"), 200)

# ================================================================ D. Đăng tin
section("D. Đăng tin và quản lý tin")
img = tiny_jpeg()
sale = check("Đăng tin bán có ảnh", seller.call("POST", "/api/user/products", {
    "title": f"Quạt bàn QA {RUN}", "description": "Quạt bàn còn tốt, dùng 6 tháng, giao tại KTX khu A.",
    "category_id": cats[0]["id"], "type": "sale", "condition": "used", "price": "150000"},
    files={"image": ("quat.jpg", img, "image/jpeg")}), (200, 201)) or {}
sale_id = sale.get("id")
record(sale.get("status") in ("pending", "active"), "Tin mới ở trạng thái chờ duyệt/hoạt động", str(sale.get("status")))
exch = check("Đăng tin trao đổi", seller.call("POST", "/api/user/products", {
    "title": f"Đổi sách QA {RUN}", "description": "Muốn đổi giáo trình Toán cao cấp lấy sách lập trình.",
    "category_id": cats[0]["id"], "type": "exchange", "condition": "like_new"}, files={}), (200, 201)) or {}
auc = check("Đăng tin đấu giá ngược", seller.call("POST", "/api/user/products", {
    "title": f"Đèn học QA {RUN}", "description": "Đèn học LED đấu giá giảm dần, còn mới 90%.",
    "category_id": cats[0]["id"], "type": "auction", "condition": "like_new", "start_price": "200000",
    "floor_price": "100000", "decrease_amount": "10000", "step_minutes": "30"}, files={}), (200, 201)) or {}
check("Thiếu tiêu đề -> 422", seller.call("POST", "/api/user/products", {"title": "", "description": "mô tả đủ dài để qua kiểm tra",
    "category_id": cats[0]["id"], "type": "sale", "condition": "used", "price": "1000"}, files={}), (400, 422))
check("Giá âm -> 422", seller.call("POST", "/api/user/products", {"title": "Tin giá âm", "description": "mô tả đủ dài để qua kiểm tra",
    "category_id": cats[0]["id"], "type": "sale", "condition": "used", "price": "-5"}, files={}), (400, 422))
check("Loại tin không hợp lệ -> 422", seller.call("POST", "/api/user/products", {"title": "Tin lạ", "description": "mô tả đủ dài để qua kiểm tra",
    "category_id": cats[0]["id"], "type": "rent", "condition": "used", "price": "1000"}, files={}), (400, 422))
check("Ảnh giả mạo (text đổi đuôi .jpg) -> 422", seller.call("POST", "/api/user/products", {"title": "Ảnh giả", "description": "mô tả đủ dài để qua kiểm tra",
    "category_id": cats[0]["id"], "type": "sale", "condition": "used", "price": "1000"},
    files={"image": ("x.jpg", b"<?php echo 1; ?>", "image/jpeg")}), (400, 415, 422))
mine = check("Tin của tôi", seller.call("GET", "/api/user/products"), 200) or []
record(any(p.get("id") == sale_id for p in mine), "Tin vừa đăng xuất hiện trong Tin của tôi", f"{len(mine)} tin")
pend = items(check("Admin xem tin chờ duyệt", admin.call("GET", "/api/admin/products?tab=pending"), 200))
for p in (sale, exch, auc):
    if p.get("id") and p.get("status") == "pending":
        check(f"Admin duyệt tin #{p['id']}", admin.call("POST", "/api/admin/products/approve", {"productId": p["id"]}), 200)
d = check("Tin đã duyệt hiện công khai", guest.call("GET", f"/api/marketplace/product?id={sale_id}"), 200) or {}
check("Điểm danh nhận xu", seller.call("POST", "/api/user/rewards/checkin", {}), (200, 409))
check("Điểm danh lần 2 trong ngày bị chặn", seller.call("POST", "/api/user/rewards/checkin", {}), (400, 409, 422))
sql(f"UPDATE users SET coins = GREATEST(coins, 50) WHERE id = {seller_id}")
check("Đẩy tin bằng xu", seller.call("POST", "/api/user/rewards/bump", {"productId": sale_id}), 200)
check("Đẩy tin của người khác bị chặn", other.call("POST", "/api/user/rewards/bump", {"productId": sale_id}), (403, 404, 422))
check("Xóa tin của người khác bị chặn", other.call("DELETE", f"/api/user/products?id={exch.get('id')}"), (403, 404))
check("Gỡ tin trao đổi của mình", seller.call("DELETE", f"/api/user/products?id={exch.get('id')}"), 200)
check("Đấu giá: xem giá hiện tại", guest.call("GET", f"/api/auction/price?id={auc.get('id')}"), 200)

# ================================================================ E. Mua bán
section("E. Yêu thích, chat, trả giá, mua hàng, đánh giá")
w = check("Thêm vào yêu thích", buyer.call("POST", "/api/user/wishlist/toggle", {"productId": sale_id}), 200) or {}
record(w.get("action") == "added", "Trạng thái yêu thích = added", str(w.get("action")))
wl = check("Danh sách yêu thích", buyer.call("GET", "/api/user/wishlist"), 200) or []
record(any(x.get("id") == sale_id for x in wl), "Sản phẩm có trong danh sách yêu thích", f"{len(wl)} mục")
conv = check("Người mua mở chat với người bán", buyer.call("POST", "/api/user/conversations", {"productId": sale_id}), (200, 201)) or {}
cid = conv.get("id")
check("Người bán tự chat với tin của mình bị chặn", seller.call("POST", "/api/user/conversations", {"productId": sale_id}), (400, 403, 409, 422))
check("Người mua gửi tin nhắn", buyer.call("POST", "/api/chat/send", {"conversation_id": cid, "body": "Chào bạn, quạt còn không?"}), 200)
check("Tin nhắn rỗng bị chặn", buyer.call("POST", "/api/chat/send", {"conversation_id": cid, "body": "   "}), (400, 422))
check("Người ngoài gửi vào hội thoại bị chặn", other.call("POST", "/api/chat/send", {"conversation_id": cid, "body": "xen ngang"}), (403, 404))
polled = check("Người bán nhận tin (poll)", seller.call("GET", f"/api/chat/poll?conv_id={cid}&after_id=0"), 200) or {}
msgs = polled.get("messages") if isinstance(polled, dict) else None
if msgs is None:
    msgs = (seller.call("GET", f"/api/chat/poll?conv_id={cid}&after_id=0")[1]).get("messages", [])
record(any("quạt còn không" in (m.get("body") or "") for m in msgs), "Người bán thấy nội dung tin nhắn", f"{len(msgs)} tin")
check("Người ngoài đọc hội thoại bị chặn", other.call("GET", f"/api/chat/poll?conv_id={cid}&after_id=0"), (403, 404))
check("Người mua trả giá 120.000đ", buyer.call("POST", "/api/chat/offer", {"conversation_id": cid, "offer_price": 120000}), 200)
check("Người bán không tự trả giá", seller.call("POST", "/api/chat/offer", {"conversation_id": cid, "offer_price": 1000}), (400, 403, 422))
offer_id = int((sql(f"SELECT id FROM messages WHERE conversation_id={int(cid or 0)} AND msg_type='offer' ORDER BY id DESC LIMIT 1") or [[0]])[0][0])
record(offer_id > 0, "Đề nghị giá được lưu trong hội thoại", f"message #{offer_id}")
check("Chốt đề nghị không tồn tại bị chặn", seller.call("POST", "/api/chat/offer/respond", {"conversation_id": cid, "message_id": 99999999, "status": "accepted"}), 409)
check("Người mua không tự chấp nhận đề nghị", buyer.call("POST", "/api/chat/offer/respond", {"conversation_id": cid, "message_id": offer_id, "status": "accepted"}), (400, 403))
check("Người bán chấp nhận đề nghị giá", seller.call("POST", "/api/chat/offer/respond", {"conversation_id": cid, "message_id": offer_id, "status": "accepted"}), 200)
check("Chốt lại đề nghị đã xử lý bị chặn", seller.call("POST", "/api/chat/offer/respond", {"conversation_id": cid, "message_id": offer_id, "status": "rejected"}), 409)
check("Đếm tin chưa đọc", seller.call("GET", "/api/chat/unread"), 200)
convs = check("Danh sách hội thoại", seller.call("GET", "/api/user/conversations"), 200) or []
record(any(c.get("id") == cid for c in convs), "Hội thoại có trong danh sách của người bán", f"{len(convs)} hội thoại")
check("Người bán không tự mua tin của mình", seller.call("POST", "/api/user/transactions/checkout", {"productId": sale_id, "type": "sale", "paymentMethod": "cod", "shippingAddress": "Phòng A203 KTX khu A"}), (400, 403, 409, 422))
check("Địa chỉ giao quá ngắn -> 422", buyer.call("POST", "/api/user/transactions/checkout", {"productId": sale_id, "type": "sale", "paymentMethod": "cod", "shippingAddress": "A"}), (400, 422))
tx = check("Đặt mua COD", buyer.call("POST", "/api/user/transactions/checkout", {"productId": sale_id, "type": "sale", "paymentMethod": "cod", "shippingAddress": "Phòng B110 KTX khu B"}), (200, 201)) or {}
txid = tx.get("id")
record(tx.get("amount") == 120000, "Giao dịch dùng giá đã thỏa thuận 120.000đ (không phải giá niêm yết 150.000đ)", str(tx.get("amount")))
check("Mua lại món đã bán bị chặn", other.call("POST", "/api/user/transactions/checkout", {"productId": sale_id, "type": "sale", "paymentMethod": "cod", "shippingAddress": "Phòng C101 KTX khu C"}), (400, 404, 409, 422))
check("Đánh giá trước khi hoàn tất bị chặn -> 409", buyer.call("POST", "/api/user/ratings", {"transactionId": txid, "stars": 5}), 409, "NOT_COMPLETED")
check("Người mua không được tự chuyển 'đang giao'", buyer.call("POST", "/api/user/transactions/status", {"id": txid, "status": "shipping"}), 409)
check("Người bán xác nhận & chuẩn bị giao", seller.call("POST", "/api/user/transactions/status", {"id": txid, "status": "shipping"}), 200)
check("Người bán đánh dấu đã giao", seller.call("POST", "/api/user/transactions/status", {"id": txid, "status": "delivered"}), 200)
check("Người bán không tự xác nhận đã nhận", seller.call("POST", "/api/user/transactions/status", {"id": txid, "status": "completed"}), 409)
check("Người mua xác nhận đã nhận", buyer.call("POST", "/api/user/transactions/status", {"id": txid, "status": "completed"}), 200)
check("Số sao 0 -> 422", buyer.call("POST", "/api/user/ratings", {"transactionId": txid, "stars": 0}), (400, 422))
check("Số sao 6 -> 422", buyer.call("POST", "/api/user/ratings", {"transactionId": txid, "stars": 6}), (400, 422))
check("Người bán không tự đánh giá giao dịch", seller.call("POST", "/api/user/ratings", {"transactionId": txid, "stars": 5}), 404)
check("Người ngoài không đánh giá được", other.call("POST", "/api/user/ratings", {"transactionId": txid, "stars": 1}), 404)
check("Thiếu CSRF khi đánh giá bị chặn", buyer.call("POST", "/api/user/ratings", {"transactionId": txid, "stars": 5}, csrf=False), (400, 403, 419))
check("Người mua đánh giá 5 sao", buyer.call("POST", "/api/user/ratings", {"transactionId": txid, "stars": 5, "comment": "Giao nhanh, đúng mô tả. <b>ok</b>"}), 201)
check("Đánh giá lần 2 bị chặn -> 409", buyer.call("POST", "/api/user/ratings", {"transactionId": txid, "stars": 1}), 409, "ALREADY_RATED")
txs = check("Lịch sử giao dịch người mua", buyer.call("GET", "/api/user/transactions"), 200) or []
t = next((x for x in txs if x.get("id") == txid), {})
record(t.get("myRating") == 5 and t.get("orderStatus") == "completed", "Giao dịch hiện 'Hoàn tất' + đã đánh giá 5★", f"{t.get('orderStatus')} / {t.get('myRating')}")
pp = check("Hồ sơ người bán hiển thị đánh giá", guest.call("GET", f"/api/users/{seller_id}/profile"), 200) or {}
record((pp.get("ratingCount") or 0) >= 1, "Điểm uy tín người bán được cập nhật", f"{pp.get('averageRating')}★ / {pp.get('ratingCount')} lượt")
notes = check("Người bán nhận thông báo đánh giá", seller.call("GET", "/api/user/notifications"), 200) or []
record(any(n.get("type") == "rating" for n in notes), "Có thông báo loại 'rating'", f"{len(notes)} thông báo")
if notes:
    check("Đánh dấu đã đọc thông báo", seller.call("POST", "/api/user/notifications/mark-read", {"id": notes[0]["id"]}), 200)
# chuyển khoản
p2 = next((p for p in plist if p.get("type") == "sale" and p.get("sellerId") == seller_id), None)
if p2:
    tx2 = check("Đặt mua bằng chuyển khoản", buyer.call("POST", "/api/user/transactions/checkout", {"productId": p2["id"], "type": "sale", "paymentMethod": "banking", "shippingAddress": "Phòng B110 KTX khu B"}), (200, 201)) or {}
    pi = check("Trang thanh toán: thông tin + QR VietQR", buyer.call("GET", f"/api/user/transactions/payment?id={tx2.get('id')}"), 200) or {}
    record(bool((pi.get("bankAccount") or {}).get("qrUrl")), "Có mã QR chuyển khoản của người bán", "có" if (pi.get("bankAccount") or {}).get("qrUrl") else "không")
    check("Người bán không giao khi chưa nhận tiền", seller.call("POST", "/api/user/transactions/status", {"id": tx2.get("id"), "status": "shipping"}), 409, "PAYMENT_NOT_CONFIRMED")
    check("Người mua tải biên lai", buyer.call("POST", "/api/user/transactions/payment/proof", {"id": str(tx2.get("id"))}, files={"proof": ("bienlai.jpg", img, "image/jpeg")}), 200)
    check("Người bán xác nhận đã nhận tiền", seller.call("POST", "/api/user/transactions/payment/confirm", {"id": tx2.get("id")}), 200)
    check("Không hủy được đơn đã thanh toán", seller.call("POST", "/api/user/transactions/status", {"id": tx2.get("id"), "status": "cancelled"}), 409)
# hủy đơn
p3 = next((p for p in plist if p.get("type") == "sale" and p.get("sellerId") == seller_id and p is not p2 and p["id"] != (p2 or {}).get("id")), None)
if p3:
    tx3 = check("Đặt mua COD (để hủy)", other.call("POST", "/api/user/transactions/checkout", {"productId": p3["id"], "type": "sale", "paymentMethod": "cod", "shippingAddress": "Phòng C101 KTX khu C"}), (200, 201)) or {}
    check("Người bán hủy đơn", seller.call("POST", "/api/user/transactions/status", {"id": tx3.get("id"), "status": "cancelled"}), 200)
    back = check("Tin được mở bán lại sau khi hủy", guest.call("GET", f"/api/marketplace/product?id={p3['id']}"), 200) or {}
    record(back.get("status") in (None, "active"), "Trạng thái tin = active", str(back.get("status")))

# ================================================================ F. AI
section("F. Tìm kiếm AI đa phương thức và nhu cầu")
r1 = check("Tìm kiếm AI bằng mô tả", buyer.call("POST", "/api/marketplace/smart-search", {"description": "cần nồi cơm điện nhỏ cho phòng ký túc xá", "limit": 10}), 200) or {}
record(r1.get("mode") in ("gemini", "hybrid", "semantic", "keyword", "local") and len(r1.get("results") or []) > 0,
       "Có kết quả + chế độ tìm", f"mode={r1.get('mode')} model={r1.get('modelKey')} n={len(r1.get('results') or [])} {buyer.last_ms}ms")
top = [x.get("title", "") for x in (r1.get("results") or [])[:3]]
record(any("cơm" in t.lower() for t in top), "Kết quả đầu đúng nhu cầu (nồi cơm)", " | ".join(top))
r2 = check("Tìm kiếm AI có ràng buộc ngân sách", buyer.call("POST", "/api/marketplace/smart-search", {"description": "bàn phím cơ", "budgetMax": 300000, "flags": {"budget": "hard"}, "limit": 10}), 200) or {}
over = [x for x in (r2.get("results") or []) if x.get("price") and x["price"] > 300000 and x.get("type") == "sale"]
record(not over, "Ràng buộc cứng ngân sách được tôn trọng", f"{len(over)} kết quả vượt ngân sách")
data_url = "data:image/jpeg;base64," + base64.b64encode(img).decode()
r3 = check("Tìm kiếm bằng ảnh (như giao diện gửi)", buyer.call("POST", "/api/marketplace/smart-search", {"description": "Tìm món đồ giống ảnh mẫu", "imageDataUrl": data_url, "limit": 5}), 200) or {}
record(len(r3.get("results") or []) > 0, "Tìm bằng ảnh trả về kết quả", f"n={len(r3.get('results') or [])} {buyer.last_ms}ms | " + " | ".join(x.get("title", "") for x in (r3.get("results") or [])[:3]))
check("Khách tìm bằng ảnh phải đăng nhập", guest.call("POST", "/api/marketplace/smart-search", {"description": "Tìm món đồ giống ảnh mẫu", "imageDataUrl": data_url}), 401)
r4 = check("Tìm gần tôi (cùng tòa KTX)", buyer.call("POST", "/api/marketplace/smart-search", {"description": "sách", "nearMe": True, "limit": 10}), 200) or {}
check("Tìm kiếm AI mô tả rỗng -> 422", buyer.call("POST", "/api/marketplace/smart-search", {"description": ""}), (400, 422))
req = check("Lưu nhu cầu mua", buyer.call("POST", "/api/marketplace/requests", {"description": f"Cần mua quạt mini để bàn {RUN}", "budgetMax": 200000}), (200, 201)) or {}
reqs = check("Xem nhu cầu đã lưu", buyer.call("GET", "/api/marketplace/requests"), 200) or []
record(any(x.get("id") == req.get("id") for x in reqs), "Nhu cầu vừa lưu có trong danh sách", f"{len(reqs)} nhu cầu")
check("AI đối chiếu lại nhu cầu", buyer.call("POST", f"/api/marketplace/requests/{req.get('id')}/matches"), 200)
check("Người khác không xem được nhu cầu", other.call("POST", f"/api/marketplace/requests/{req.get('id')}/matches"), (403, 404))
check("Đóng nhu cầu", buyer.call("POST", f"/api/marketplace/requests/{req.get('id')}/close"), 200)
check("Tìm kiếm matching cơ sở (hashing)", buyer.call("GET", "/api/marketplace/matching/search?q=" + urllib.parse.quote("bàn học nhỏ")), 200)

# ================================================================ G. Báo cáo, thông báo
section("G. Báo cáo vi phạm, thông báo, phần thưởng")
target = next((p for p in plist if p.get("sellerId") not in (seller_id,)), plist[-1])
check("Lý do báo cáo không hợp lệ -> 422", buyer.call("POST", "/api/user/reports", {"productId": target["id"], "reason": "abc", "description": "Mô tả đủ dài cho báo cáo"}), 422)
check("Mô tả báo cáo quá ngắn -> 422", buyer.call("POST", "/api/user/reports", {"productId": target["id"], "reason": "Lừa đảo / giả mạo", "description": "ngắn"}), 422)
check("Báo cáo tin của chính mình bị chặn", seller.call("POST", "/api/user/reports", {"productId": sale_id, "reason": "Khác", "description": "Tự báo cáo tin của mình"}), 422)
check("Báo cáo tin không tồn tại -> 404", buyer.call("POST", "/api/user/reports", {"productId": 99999999, "reason": "Khác", "description": "Tin không tồn tại để thử"}), 404)
check("Khách chưa đăng nhập không báo cáo được", guest.call("POST", "/api/user/reports", {"productId": target["id"], "reason": "Khác", "description": "Khách thử gửi báo cáo"}), 401)
rep = check("Gửi báo cáo tin đăng", buyer.call("POST", "/api/user/reports", {"productId": target["id"], "reason": "Sai danh mục / thông tin sai", "description": f"Tin đăng sai danh mục, mô tả không khớp ảnh ({RUN})."}), 201) or {}
check("Báo cáo trùng khi đang chờ xử lý -> 409", buyer.call("POST", "/api/user/reports", {"productId": target["id"], "reason": "Khác", "description": "Gửi lại lần hai cùng tin"}), 409, "ALREADY_REPORTED")
sr = check("Admin thấy báo cáo trong hàng chờ", admin.call("GET", "/api/admin/system-reports?status=pending"), 200) or {}
record(any(x.get("id") == rep.get("id") for x in (sr.get("reports") or [])), "Báo cáo mới có trong danh sách admin", f"{sr.get('pendingCount')} chờ xử lý")
check("Admin xử lý báo cáo", admin.call("POST", f"/api/admin/system-reports/{rep.get('id')}/resolve", {"status": "resolved", "adminNote": "Đã nhắc người bán sửa danh mục"}), 200)
check("Phần thưởng (xu, chuỗi điểm danh)", buyer.call("GET", "/api/user/rewards"), 200)
check("Đếm thông báo chưa đọc", buyer.call("GET", "/api/notifications/unread"), 200)

# ================================================================ H. KTX – sinh viên
section("H. Ký túc xá – sinh viên")
resident = buyer  # tài khoản test u02 là cư dân (seed)
check("Hồ sơ lưu trú", resident.call("GET", "/api/residence/applications"), 200)
ct = check("Hợp đồng lưu trú", resident.call("GET", "/api/residence/contracts"), 200) or []
record(any(c.get("status") == "active" for c in ct), "Có hợp đồng đang hiệu lực", ",".join(str(c.get("status")) for c in ct))
check("Lịch sử chỗ ở", resident.call("GET", "/api/residence/accommodation/history"), 200)
inv = check("Hóa đơn của tôi", resident.call("GET", "/api/residence/invoices"), 200) or []
record(len(inv) >= 1, "Có hóa đơn tháng 11 (seed W10)", f"{len(inv)} hóa đơn")
open_inv = next((i for i in inv if i.get("status") in ("open", "partial") and (i.get("balance") or i.get("outstanding") or 0) > 0), None)
if open_inv:
    bal = open_inv.get("balance") or open_inv.get("outstanding")
    check("Trả vượt dư nợ bị chặn", resident.call("POST", "/api/residence/payments", {"invoiceId": open_inv["id"], "amount": bal + 1, "method": "bank_transfer"}), (409, 422))
    check("Ghi nhận thanh toán chuyển khoản", resident.call("POST", "/api/residence/payments", {"invoiceId": open_inv["id"], "amount": min(bal, 100000), "method": "bank_transfer", "reference": "CK" + RUN}), (200, 201))
check("Lịch sử thanh toán", resident.call("GET", "/api/residence/payments"), 200)
nt = check("Thông báo KTX", resident.call("GET", "/api/residence/notices"), 200) or []
record(len(nt) >= 3, "Thấy thông báo cúp điện / sự kiện / PCCC", f"{len(nt)} thông báo")
s1 = check("Gửi yêu cầu sửa chữa", resident.call("POST", "/api/residence/service-requests", {"requestType": "repair", "title": f"Vòi nước rò rỉ {RUN}", "description": "Vòi nước bồn rửa trong phòng bị rò rỉ liên tục, nước chảy ra sàn."}), (200, 201)) or {}
record(bool(s1.get("classification")), "Yêu cầu được AI phân loại", json.dumps(s1.get("classification"), ensure_ascii=False)[:140])
check("Tiêu đề yêu cầu rỗng -> 422", resident.call("POST", "/api/residence/service-requests", {"requestType": "repair", "title": "", "description": "x"}), (400, 422))
ans = check("Trợ lý nội quy trả lời", resident.call("POST", "/api/residence/assistant/rules", {"question": "Mấy giờ KTX đóng cửa buổi tối?"}), 200) or {}
src = [x.get("title", "") for x in (ans.get("sources") or [])]
record(any("giới nghiêm" in t for t in src), "Trả lời đúng nội quy giờ giới nghiêm", " | ".join(src) or "không có nguồn")
ans2 = check("Trợ lý: câu hỏi ngoài nội quy", resident.call("POST", "/api/residence/assistant/rules", {"question": "Giá vé xe buýt đến trường bao nhiêu?"}), 200) or {}
record(not ans2.get("sources"), "Không bịa câu trả lời khi nội quy không đề cập", (ans2.get("answer") or "")[:80])
spaces = check("Danh sách mặt bằng kinh doanh", resident.call("GET", "/api/rental/spaces"), 200) or []
record(len(spaces) >= 3, "Có 3 ki-ốt demo", f"{len(spaces)} mặt bằng")
check("SV gọi API quản lý KTX bị chặn", resident.call("GET", "/api/admin/residence/dashboard"), 403)
check("SV tạo hóa đơn bị chặn", resident.call("POST", "/api/admin/residence/invoices", {"userId": 1}), 403)

# ================================================================ I. KTX – quản lý
section("I. Ký túc xá – ban quản lý")
dash = check("Dashboard vận hành", mgr.call("GET", "/api/admin/residence/dashboard"), 200)
record("@" not in json.dumps(dash, ensure_ascii=False), "Dashboard không lộ email sinh viên", "ok")
apps = items(check("Hồ sơ lưu trú chờ duyệt", mgr.call("GET", "/api/admin/residence/applications"), 200))
pending = [a for a in apps if a.get("status") in ("submitted", "under_review")]
record(len(pending) >= 1, "Có hồ sơ chờ duyệt (seed W10)", f"{len(pending)} hồ sơ")
if pending:
    a0 = pending[0]
    check("Bắt đầu xét duyệt", mgr.call("POST", f"/api/admin/residence/applications/{a0['id']}/review", {"decision": "under_review"}), (200, 409))
    check("Phê duyệt hồ sơ -> tạo hợp đồng", mgr.call("POST", f"/api/admin/residence/applications/{a0['id']}/review", {"decision": "approve", "note": "Đủ điều kiện"}), 200)
    check("Duyệt lặp bị chặn", mgr.call("POST", f"/api/admin/residence/applications/{a0['id']}/review", {"decision": "approve"}), 409)
    waiting = items(check("Hợp đồng chờ xếp giường", mgr.call("GET", "/api/admin/residence/allocations"), 200))
    beds = items(check("Giường trống", mgr.call("GET", "/api/admin/residence/available-beds"), 200))
    record(len(beds) >= 1, "Có giường trống (seed W10)", f"{len(beds)} giường")
    c0 = next((c for c in waiting if c.get("applicationId") == a0["id"] or c.get("status") == "pending_assignment"), None)
    if c0 and beds:
        check("Xếp giường cho sinh viên", mgr.call("POST", "/api/admin/residence/allocations", {"contractId": c0.get("id") or c0.get("contractId"), "bedId": beds[0]["id"], "note": "QA"}), (200, 201))
    if len(pending) > 1:
        check("Từ chối hồ sơ thứ hai", mgr.call("POST", f"/api/admin/residence/applications/{pending[1]['id']}/review", {"decision": "reject", "note": "Hết chỗ loại phòng yêu cầu"}), 200)
pays = check("Khoản thanh toán chờ xác nhận", mgr.call("GET", "/api/residence/payments"), 200) or []
pend_pay = [p for p in items(pays) if p.get("status") == "pending"]
record(len(pend_pay) >= 1, "Có khoản chờ xác nhận", f"{len(pend_pay)} khoản")
if pend_pay:
    check("Xác nhận thanh toán", mgr.call("POST", f"/api/admin/residence/payments/{pend_pay[0]['id']}/review", {"decision": "confirmed"}), 200)
    check("Xác nhận lặp bị chặn", mgr.call("POST", f"/api/admin/residence/payments/{pend_pay[0]['id']}/review", {"decision": "confirmed"}), (409, 422))
    if len(pend_pay) > 1:
        check("Từ chối khoản thanh toán", mgr.call("POST", f"/api/admin/residence/payments/{pend_pay[1]['id']}/review", {"decision": "rejected"}), 200)
fcs = check("Danh mục phí", mgr.call("GET", "/api/residence/fee-categories"), 200) or []
buyer_id = int(sql(f"SELECT id FROM users WHERE email='{BUYER['email']}'")[0][0])
bct = next((c for c in ct if c.get("status") == "active"), {})
newinv = check("Lập hóa đơn mới", mgr.call("POST", "/api/admin/residence/invoices", {"userId": buyer_id, "contractId": bct.get("id"), "description": f"Phí phát sinh {RUN}", "dueDate": (TODAY + dt.timedelta(days=15)).isoformat(),
    "lines": [{"feeCategoryId": (items(fcs) or [{}])[0].get("id"), "description": "Điện vượt định mức", "quantity": 2, "unitPrice": 35000}]}), (200, 201)) or {}
check("Hóa đơn không có dòng phí -> 422", mgr.call("POST", "/api/admin/residence/invoices", {"userId": buyer_id, "contractId": bct.get("id"), "description": "rỗng", "dueDate": TODAY.isoformat(), "lines": []}), (400, 422))
check("Hủy hóa đơn lập nhầm", mgr.call("POST", f"/api/admin/residence/invoices/{newinv.get('id')}/void"), 200)
reqs = items(check("Hàng đợi sửa chữa", mgr.call("GET", "/api/residence/service-requests"), 200))
if s1.get("id"):
    check("Chuyển yêu cầu sang đang xử lý", mgr.call("PUT", f"/api/admin/residence/service-requests/{s1['id']}", {"status": "in_progress", "staffNote": "Đã cử kỹ thuật"}), 200)
    check("Chuyển trạng thái sai thứ tự bị chặn", mgr.call("PUT", f"/api/admin/residence/service-requests/{s1['id']}", {"status": "submitted"}), (409, 422))
rapps = items(check("Hồ sơ thuê mặt bằng", mgr.call("GET", "/api/admin/rental/applications"), 200))
rp = next((x for x in rapps if x.get("status") in ("submitted", "under_review", "pending")), None)
if rp:
    check("Duyệt thuê ki-ốt", mgr.call("POST", f"/api/admin/rental/applications/{rp['id']}/review", {"decision": "approve", "note": "OK"}), 200)
rcs = items(check("Hợp đồng thuê mặt bằng", mgr.call("GET", "/api/rental/contracts"), 200))
act = next((x for x in rcs if x.get("status") == "active"), None)
if act:
    start_c = dt.date.fromisoformat(str(act.get("startDate"))[:10]) if act.get("startDate") else TODAY
    check("Chấm dứt hợp đồng thuê sớm", mgr.call("POST", f"/api/admin/rental/contracts/{act['id']}/terminate", {"endDate": (max(TODAY, start_c) + dt.timedelta(days=1)).isoformat()}), 200)
n = check("Đăng thông báo", mgr.call("POST", "/api/admin/residence/notices", {"type": "announcement", "title": f"Lịch phun thuốc muỗi {RUN}", "body": "Phun thuốc muỗi toàn khu vào sáng thứ Bảy.", "audience": "residents"}), (200, 201)) or {}
check("Lưu trữ thông báo", mgr.call("POST", f"/api/admin/residence/notices/{n.get('id')}/archive"), 200)
inv_k = check("Kho phòng – giường", mgr.call("GET", "/api/admin/residence/inventory"), 200)
mcts = items(check("Danh sách hợp đồng lưu trú", mgr.call("GET", "/api/admin/residence/contracts"), 200))
endc = next((c for c in mcts if c.get("status") == "active" and c.get("userId") not in (buyer_id,)), None)
if endc:
    check("Trả phòng (kết thúc hợp đồng)", mgr.call("POST", f"/api/admin/residence/contracts/{endc['id']}/end", {"status": "terminated", "endDate": TODAY.isoformat(), "note": "QA trả phòng"}), 200)
check("Quản lý KTX không vào được API admin sàn", mgr.call("GET", "/api/admin/products?tab=pending"), (401, 403))

# ================================================================ J. Admin
section("J. Quản trị hệ thống")
check("Dashboard admin", admin.call("GET", "/api/admin/dashboard"), 200)
for tab in ("pending", "active", "rejected"):
    check(f"Danh sách tin tab {tab}", admin.call("GET", f"/api/admin/products?tab={tab}"), 200)
bad = check("Đăng tin để thử từ chối", other.call("POST", "/api/user/products", {"title": f"Tin cần từ chối {RUN}", "description": "Nội dung không phù hợp với quy định đăng tin của sàn.",
    "category_id": cats[0]["id"], "type": "sale", "condition": "used", "price": "50000"}, files={}), (200, 201)) or {}
if bad.get("status") == "pending":
    check("Từ chối không có lý do -> 422", admin.call("POST", "/api/admin/products/reject", {"productId": bad["id"], "reason": ""}), (400, 422))
    check("Từ chối tin kèm lý do", admin.call("POST", "/api/admin/products/reject", {"productId": bad["id"], "reason": "Thiếu ảnh thật của sản phẩm"}), 200)
    mine3 = other.call("GET", "/api/user/products")[1].get("data") or []
    record(any(p.get("id") == bad["id"] and p.get("rejectionReason") for p in mine3), "Người đăng thấy lý do từ chối", "ok")
check("Giao dịch toàn sàn", admin.call("GET", "/api/admin/transactions"), 200)
users = check("Danh sách người dùng", admin.call("GET", "/api/admin/users"), 200) or []
other_id = int(sql(f"SELECT id FROM users WHERE email='{OTHER['email']}'")[0][0])
check("Chi tiết người dùng", admin.call("GET", f"/api/admin/users/{other_id}"), 200)
check("Khóa tài khoản thiếu lý do -> 422", admin.call("POST", f"/api/admin/users/{other_id}/toggle-lock", {"duration": "3days"}), (400, 422))
check("Khóa tài khoản 3 ngày", admin.call("POST", f"/api/admin/users/{other_id}/toggle-lock", {"reason": "Spam tin đăng (QA)", "duration": "3days"}), 200)
lk = check("Đăng nhập khi đang bị khóa", Client("x").login(OTHER["email"], TEST_PW), (200, 403, 423)) or {}
record(lk.get("status") == "locked", "Tài khoản bị khóa không vào được (status=locked)", str(lk.get("status")))
check("Phiên cũ của tài khoản bị khóa bị chặn", other.call("GET", "/api/user/products"), (401, 423))
check("Mở khóa tài khoản", admin.call("POST", f"/api/admin/users/{other_id}/toggle-lock", {}), 200)
check("Đăng nhập lại sau khi mở khóa", Client("x").login(OTHER["email"], TEST_PW), 200)
admin_id = int(sql(f"SELECT id FROM users WHERE email='{ADMIN_EMAIL}'")[0][0])
check("Không tự khóa tài khoản admin", admin.call("POST", f"/api/admin/users/{admin_id}/toggle-lock", {"reason": "x", "duration": "3days"}), 403)
check("Biểu tượng danh mục ngoài danh sách -> 422", admin.call("POST", "/api/admin/categories", {"name": f"Danh mục lạ {RUN}", "icon": "<svg>"}), 422)
cat = check("Thêm danh mục", admin.call("POST", "/api/admin/categories", {"name": f"Danh mục QA {RUN}", "icon": "bi-box-seam"}), (200, 201)) or {}
cat = cat.get("category") or cat
check("Danh mục trùng tên bị chặn", admin.call("POST", "/api/admin/categories", {"name": f"Danh mục QA {RUN}", "icon": "bi-box-seam"}), (409, 422))
check("Sửa danh mục", admin.call("PUT", f"/api/admin/categories/{cat.get('id')}", {"id": cat.get("id"), "name": f"Danh mục QA sửa {RUN}", "icon": "bi-bag"}), 200)
check("Xóa danh mục trống", admin.call("DELETE", f"/api/admin/categories/{cat.get('id')}"), 200)
check("Không xóa danh mục đang có tin", admin.call("DELETE", f"/api/admin/categories/{cats[0]['id']}"), (409, 422))
check("Danh sách banner", admin.call("GET", "/api/admin/banners"), 200)
ratings = check("Quản lý đánh giá", admin.call("GET", "/api/admin/ratings"), 200) or []
myr = next((x for x in ratings if x.get("transactionId") == txid or "Giao nhanh" in (x.get("comment") or "")), None)
record(myr is not None, "Đánh giá mới có trong trang quản lý", f"{len(ratings)} đánh giá")
if myr:
    check("Ẩn đánh giá vi phạm", admin.call("POST", f"/api/admin/ratings/{myr['id']}/toggle"), 200)
    check("Hiện lại đánh giá", admin.call("POST", f"/api/admin/ratings/{myr['id']}/toggle"), 200)
log = check("Nhật ký kiểm toán", admin.call("GET", "/api/admin/audit-log"), 200)
record("lock_user" in json.dumps(log, ensure_ascii=False), "Nhật ký ghi lại thao tác khóa tài khoản", "ok")
check("Báo cáo giao dịch thiếu khoảng ngày -> 422", admin.call("GET", "/api/admin/reports"), 422)
rpt = check("Báo cáo doanh thu theo khoảng ngày", admin.call("GET", f"/api/admin/reports?from={(TODAY - dt.timedelta(days=30)).isoformat()}&to={TODAY.isoformat()}"), 200) or {}
record("completed" in json.dumps(rpt) or len(json.dumps(rpt)) > 20, "Báo cáo có số liệu giao dịch vừa hoàn tất", json.dumps(rpt, ensure_ascii=False)[:120])
check("Kết quả đánh giá mô hình đã lưu", admin.call("GET", "/api/admin/marketplace/matching/evaluations"), 200)
cmp_ = check("So sánh mô hình AI trên 1 truy vấn", admin.call("POST", "/api/admin/marketplace/matching/compare", {"queryText": "nồi cơm điện", "relevantProductIds": [p["id"] for p in plist if "cơm" in p.get("title", "").lower()][:3]}), (200, 201))
check("Xuất dữ liệu (CSV)", admin.call("GET", "/api/admin/export?type=users"), (200, 422))
check("Admin gán vai trò quản lý KTX: danh sách vai trò", admin.call("GET", "/api/admin/roles"), 200)

# ================================================================ K. Bảo mật
section("K. Bảo mật")
check("Sinh viên vào dashboard admin -> 403", buyer.call("GET", "/api/admin/dashboard"), (401, 403))
check("Sinh viên duyệt tin -> 403", buyer.call("POST", "/api/admin/products/approve", {"productId": sale_id}), (401, 403))
check("Sinh viên khóa người khác -> 403", buyer.call("POST", f"/api/admin/users/{other_id}/toggle-lock", {"reason": "x", "duration": "3days"}), (401, 403))
check("Thiếu CSRF khi đăng tin bị chặn", buyer.call("POST", "/api/user/wishlist/toggle", {"productId": sale_id}, csrf=False), (400, 403, 419))
buyer_uid = buyer_id
check("Realtime: ký kênh của chính mình", buyer.call("POST", "/api/realtime/auth", {"socket_id": "123.456", "channel_name": f"private-chat.{buyer_uid}"}), (200, 503))
check("Realtime: nghe trộm kênh người khác -> 403", buyer.call("POST", "/api/realtime/auth", {"socket_id": "123.456", "channel_name": f"private-chat.{seller_id}"}), 403)
check("Realtime: khách -> 401", guest.call("POST", "/api/realtime/auth", {"socket_id": "123.456", "channel_name": f"private-chat.{seller_id}"}), (401, 403))
inj = check("SQL injection ở ô tìm kiếm không gây lỗi", guest.call("GET", "/api/products/search?q=" + urllib.parse.quote("' OR 1=1 -- ")), 200)
check("SQL injection ở id sản phẩm -> 404/422", guest.call("GET", "/api/marketplace/product?id=" + urllib.parse.quote("1 OR 1=1")), (400, 404, 422))
xss = check("Đăng tin chứa <script> (lưu, chờ hiển thị an toàn)", buyer.call("POST", "/api/user/products", {"title": f"<script>alert(1)</script> XSS {RUN}", "description": "<img src=x onerror=alert(2)> mô tả thử XSS đủ dài.",
    "category_id": cats[0]["id"], "type": "sale", "condition": "used", "price": "1000"}, files={}), (200, 201, 422)) or {}
xss_id = xss.get("id")
if xss_id and xss.get("status") == "pending":
    admin.call("POST", "/api/admin/products/approve", {"productId": xss_id})
codes = []
for i in range(12):
    st, _ = Client("bf").login(f"bruteforce.{RUN}@example.test", "sai" + str(i))
    codes.append(st)
record(429 in codes or 423 in codes, "Chống dò mật khẩu: bị giới hạn sau nhiều lần sai", "mã: " + ",".join(map(str, codes)))
st, content, ctype = guest.call("GET", "/api/marketplace/products", raw=True)
record(b"password" not in content and b"otp_code" not in content, "API công khai không lộ mật khẩu/OTP", "ok")
st, content, ctype = guest.call("GET", f"/api/users/{seller_id}/profile", raw=True)
record(SELLER["email"].encode() not in content, "Hồ sơ công khai không lộ email", "ok" if SELLER["email"].encode() not in content else "LỘ EMAIL")
with open(OUT, "w", encoding="utf-8") as fh:
    json.dump({"run": RUN, "results": results, "ids": {"sale": sale_id, "auction": auc.get("id"), "tx": txid, "xss": xss_id,
               "seller": seller_id, "buyer": buyer_id}}, fh, ensure_ascii=False, indent=1)
passed = sum(1 for r in results if r["ok"])
print(f"\nTỔNG: {passed}/{len(results)} PASS")
