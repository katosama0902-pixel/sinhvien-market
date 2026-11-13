# BÁO CÁO TIẾN ĐỘ TUẦN 4 — ĐỒ ÁN CHUYÊN NGÀNH

- **Dự án**: Nâng cấp hệ thống **SinhVienMarket** sang Next.js 16 (App Router) + React 19 + TypeScript (`strict`) + Tailwind CSS 4 kết hợp AI Ghép nối Đa phương thức.
- **Nội dung thực hiện Tuần 4**: Nghiên cứu, xây dựng bộ dữ liệu thực nghiệm (`44 OFFER × 31 REQUEST`), cài đặt thực nghiệm đối đầu trực tiếp giữa **Google Gemini Embedding 2** và **Jina-CLIP-v2** (cùng 2 đối chứng `B1 BM25` và `B2 Text-Only`), xây dựng cơ chế gợi ý điều kiện thay thế (**Counterfactual Recommendation**) bảo toàn 100% điều kiện bắt buộc (`hard`), và hoàn thiện giao diện người dùng **Cần mua & Ghép nối AI (`/requests`)**.
- **Nhánh Git làm việc**: `feature/nextjs-frontend` (trên kho lưu trữ nhóm `sinhvien-market` và kho dự phòng cá nhân `shingetsu194/sinhvien-market`).

---

## 1. Tổng hợp các hạng mục đã hoàn thành trong Tuần 4

| STT | Hạng mục công việc | Đường dẫn mã nguồn / Tài liệu | Trạng thái |
|---|---|---|---|
| 1 | **Đặc tả bài toán REQUEST–OFFER & Hợp đồng API AI** | `docs/AI_REQUEST_OFFER_SPEC.md`, `docs/AI_MATCHING_API_CONTRACT.md` | Hoàn thành |
| 2 | **Xây dựng bộ dữ liệu chuẩn hóa & Tạo ảnh sản phẩm tham chiếu** | `ai-lab/data/offers.json` (`44 OFFER`), `ai-lab/data/requests.json` (`31 REQUEST`), `public/ai-samples/*.jpg` (6 ảnh thật) | Hoàn thành |
| 3 | **Phòng thí nghiệm AI (`ai-lab`) & Thực nghiệm 5 phương pháp** | `ai-lab/run_benchmark.py`, `ai-lab/results/benchmark_summary.json`, `docs/BAO_CAO_SO_SANH_EMBEDDING.md` | Hoàn thành |
| 4 | **Động cơ Ghép nối Đa phương thức & Counterfactual trên Next.js** | `frontend/lib/ai/matching-engine.ts`, `frontend/lib/ai/dataset.ts`, `frontend/lib/ai/benchmark-results.json` | Hoàn thành |
| 5 | **Giao diện Sinh viên: Đăng tin Cần mua & Ghép nối AI (`/requests`)** | `frontend/app/requests/page.tsx` | Hoàn thành |
| 6 | **Kiểm thử tự động Đơn vị (Vitest) & E2E (Playwright)** | `frontend/tests/unit/matching.test.ts` (6 test), `frontend/tests/e2e/requests.spec.ts` (3 luồng E2E) | Hoàn thành |

---

## 2. Kết quả Thực nghiệm Mô hình Embedding (`44 OFFER × 31 REQUEST`)

Hệ thống đánh giá trên cùng một tập dữ liệu gán nhãn và cùng bộ lọc điều kiện bắt buộc (`hard` vs `soft`):

| Phương pháp / Mô hình | Mã mô hình | P@1 | P@3 | Recall@5 | MRR | Multimodal P@1 (Có ảnh) | Khôi phục Counterfactual | Vi phạm Hard |
|---|---|---:|---:|---:|---:|---:|---:|---:|
| **B1 — Keyword BM25 (Tiếng Việt)** | `keyword-bm25-vi` | 68.2% | 31.8% | 59.6% | 0.727 | 0.0% | 83.3% (5/6) | **0** |
| **B2 — Gemini Text-Only** | `gemini-embedding-2` (`768d`) | 81.8% | 31.8% | 63.2% | 0.818 | 40.0% | 83.3% (5/6) | **0** |
| **B2 — Jina Text-Only** | `jina-clip-v2` (`1024d`) | 77.3% | 33.3% | 61.1% | 0.773 | 0.0% | 100.0% (6/6) | **0** |
| **M1 — Gemini Embedding 2 (Multimodal)** | `gemini-embedding-2` (`768d`) | 95.5% | 39.4% | 81.4% | 0.955 | **100.0%** | 83.3% (5/6) | **0** |
| **M2 — Jina-CLIP-v2 (Multimodal)** | `jina-clip-v2` (`1024d`) | **100.0%** | **42.4%** | **83.0%** | **1.000** | **100.0%** | **100.0%** (6/6) | **0** |

---

## 3. Các cải tiến UX/UI trên trang `/requests` sau khi kiểm thử thực tế

1. **Thiết kế thân thiện cho sinh viên**:
   - Đổi toàn bộ thuật ngữ học thuật sang ngôn ngữ gần gũi (*"Độ phù hợp 92%"*, *"Tiêu chí cố định"*, *"Có thể linh hoạt"*, *"Gợi ý điều chỉnh để tìm thấy hàng"*).
   - Ẩn bảng số liệu thực nghiệm chuyên sâu vào ngăn kéo **"Chế độ Nghiên cứu / Đồ án (5 mô hình AI)"** để không gây nhiễu thông tin cho người dùng phổ thông.
2. **Bộ lọc & Tìm kiếm chính xác tuyệt đối**:
   - Hỗ trợ bấm lại vào tag danh mục đang chọn (hoặc nút **Bỏ lọc**) để hủy lọc về *"Tất cả"*.
   - Hỗ trợ nhấn phím `Enter` hoặc bấm nút **Tìm kiếm** trong ô tìm kiếm kèm hiển thị số lượng kết quả khớp.
   - Đồng bộ bộ lọc 2 cột: Khi bộ lọc đang chọn không có tin Cần mua nào khớp (0 tin), khung bên phải tự động hiển thị trạng thái trống thay vì giữ kết quả của tin cũ.
   - Bổ sung bộ lọc từ khóa đối xứng (`QUERY_CONCEPT_GROUPS`) giúp loại bỏ hoàn toàn các sản phẩm khác loại mặc dù cùng danh mục (ví dụ: tìm *"sách giải tích"* chỉ ra đúng sách Giải tích, không lẫn sách TOEIC hay Triết học).
