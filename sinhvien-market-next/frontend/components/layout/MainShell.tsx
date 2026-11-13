"use client";

import {
  createContext,
  useCallback,
  useContext,
  useEffect,
  useRef,
  useState,
  type ReactNode,
} from "react";
import Link from "next/link";
import { usePathname, useRouter } from "next/navigation";
import {
  Bell,
  ChevronDown,
  Coins,
  Gift,
  Grid,
  Heart,
  Home,
  LogIn,
  LogOut,
  Menu,
  MessageCircle,
  PlusCircle,
  Search,
  ShieldCheck,
  ShoppingBag,
  Sparkles,
  ClipboardList,
  UserCheck,
  UserPlus,
  X,
} from "lucide-react";
import { FlashBanner } from "@/components/auth/FlashBanner";
import { PortalAccessDialog } from "@/components/auth/PortalAccessDialog";
import { ThemeToggle } from "@/components/ui/ThemeToggle";
import { getCurrentUser, logout } from "@/lib/api/auth";
import type { AuthUser } from "@/lib/api/types";
import { cn } from "@/lib/cn";
import { setFlash } from "@/lib/flash";

interface MainShellContextValue {
  user: AuthUser | null;
  loadingUser: boolean;
  refreshUser: () => void;
}

const MainShellContext = createContext<MainShellContextValue>({
  user: null,
  loadingUser: true,
  refreshUser: () => {},
});

export function useMainShell() {
  return useContext(MainShellContext);
}

interface MainShellProps {
  children: ReactNode;
  initialSearch?: string;
}

interface QuickAccessTarget {
  href: string;
}

interface PortalNotice extends QuickAccessTarget {
  title: string;
  description: string;
  confirmLabel: string;
  intent: "close" | "switch-account" | "open-admin";
}

export function MainShell({ children, initialSearch = "" }: MainShellProps) {
  const router = useRouter();
  const pathname = usePathname();

  const [user, setUser] = useState<AuthUser | null>(null);
  const [loadingUser, setLoadingUser] = useState(true);
  const [searchText, setSearchText] = useState(initialSearch);
  const [mobileOpen, setMobileOpen] = useState(false);
  const [userMenuOpen, setUserMenuOpen] = useState(false);
  const [notifOpen, setNotifOpen] = useState(false);
  const [matchingMenuOpen, setMatchingMenuOpen] = useState(false);
  const [ktxMenuOpen, setKtxMenuOpen] = useState(false);
  const [loggingOut, setLoggingOut] = useState(false);
  const [portalNotice, setPortalNotice] = useState<PortalNotice | null>(null);

  const userMenuRef = useRef<HTMLDivElement | null>(null);
  const notifMenuRef = useRef<HTMLDivElement | null>(null);
  const matchingMenuRef = useRef<HTMLDivElement | null>(null);
  const ktxMenuRef = useRef<HTMLDivElement | null>(null);

  const loadUser = useCallback(() => {
    getCurrentUser()
      .then((u) => {
        setUser(u);
      })
      .catch(() => {
        setUser(null);
      })
      .finally(() => {
        setLoadingUser(false);
      });
  }, []);

  useEffect(() => {
    loadUser();
  }, [loadUser]);

  useEffect(() => {
    function handleOutsideClick(e: MouseEvent) {
      if (
        userMenuRef.current &&
        !userMenuRef.current.contains(e.target as Node)
      ) {
        setUserMenuOpen(false);
      }
      if (
        notifMenuRef.current &&
        !notifMenuRef.current.contains(e.target as Node)
      ) {
        setNotifOpen(false);
      }
      if (
        matchingMenuRef.current &&
        !matchingMenuRef.current.contains(e.target as Node)
      ) {
        setMatchingMenuOpen(false);
      }
      if (
        ktxMenuRef.current &&
        !ktxMenuRef.current.contains(e.target as Node)
      ) {
        setKtxMenuOpen(false);
      }
    }
    window.addEventListener("mousedown", handleOutsideClick);
    return () => window.removeEventListener("mousedown", handleOutsideClick);
  }, []);

  const handleSearchSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    const q = searchText.trim();
    setMobileOpen(false);
    if (q) {
      router.push(`/products?q=${encodeURIComponent(q)}`);
    } else {
      router.push("/products");
    }
  };

  const handleLogout = async () => {
    if (loggingOut) return;
    setLoggingOut(true);
    setUserMenuOpen(false);
    setMobileOpen(false);
    try {
      await logout();
      setUser(null);
      setFlash({ variant: "success", message: "Đã đăng xuất thành công." });
      router.push("/login-role");
    } finally {
      setLoggingOut(false);
    }
  };

  const handleQuickAccessClick = async ({ href }: QuickAccessTarget) => {
    let activeUser = user;
    if (loadingUser) {
      try {
        activeUser = await getCurrentUser();
        setUser(activeUser);
      } catch {
        setFlash({
          variant: "danger",
          message: "Chưa thể xác minh phiên đăng nhập. Vui lòng thử lại.",
        });
        return;
      }
    }

    if (!activeUser) {
      router.push(href);
      return;
    }

    if (href === "/admin-login") {
      if (activeUser.role === "admin") {
        setPortalNotice({
          href: "/admin",
          title: "Bạn đã đăng nhập với tài khoản Quản trị viên",
          description:
            "Phiên quản trị của bạn đang hoạt động. Bạn có thể vào thẳng trang quản trị mà không cần đăng nhập lại.",
          confirmLabel: "Vào trang quản trị",
          intent: "open-admin",
        });
      } else {
        setPortalNotice({
          href,
          title: "Không thể truy cập cổng quản trị",
          description:
            "Cổng này chỉ dành cho tài khoản Quản trị viên được cấp quyền. Tài khoản Sinh viên hiện tại không thể truy cập hoặc thực hiện thao tác quản trị.",
          confirmLabel: "Đã hiểu",
          intent: "close",
        });
      }
      return;
    }

    const accountType =
      activeUser.role === "admin" ? "Quản trị viên" : "Sinh viên";
    setPortalNotice({
      href,
      title: "Bạn đã đăng nhập rồi",
      description: `Bạn đang đăng nhập với tài khoản ${accountType}. Nếu muốn chuyển sang cổng đăng nhập này, hãy đăng xuất tài khoản hiện tại trước.`,
      confirmLabel: "Đăng xuất và tiếp tục",
      intent: "switch-account",
    });
  };

  const handlePortalNoticeConfirm = async () => {
    if (!portalNotice) return;
    if (portalNotice.intent === "close") {
      setPortalNotice(null);
      return;
    }
    if (portalNotice.intent === "open-admin") {
      const { href } = portalNotice;
      setPortalNotice(null);
      router.push(href);
      return;
    }

    setLoggingOut(true);
    try {
      await logout();
      const { href } = portalNotice;
      setUser(null);
      setPortalNotice(null);
      setFlash({
        variant: "success",
        message: "Đã đăng xuất. Bạn có thể tiếp tục sang cổng đã chọn.",
      });
      router.push(href);
    } catch {
      setPortalNotice(null);
      setFlash({
        variant: "danger",
        message: "Chưa thể đăng xuất. Vui lòng thử lại sau.",
      });
    } finally {
      setLoggingOut(false);
    }
  };

  const isAdmin = user?.role === "admin";
  const avatarLetter = user?.name ? user.name.trim()[0].toUpperCase() : "U";

  return (
    <MainShellContext.Provider
      value={{ user, loadingUser, refreshUser: loadUser }}
    >
      <div className="flex min-h-screen flex-col overflow-x-clip bg-bg text-fg">
        <nav className="sticky top-0 z-50 border-b border-white/10 bg-linear-to-r from-[#0f0c29] via-[#302b63] to-[#24243e] text-white shadow-lg">
          <div className="mx-auto max-w-7xl px-4">
            <div className="flex h-16 items-center justify-between gap-2.5">
              <Link
                href="/"
                className="flex shrink-0 items-center gap-2 text-white no-underline"
              >
                <div className="flex h-9 w-9 items-center justify-center rounded-[10px] bg-linear-to-br from-indigo-500 via-purple-500 to-pink-500 text-white shadow-[0_4px_12px_rgba(99,102,241,0.5)]">
                  <ShoppingBag className="h-5 w-5" />
                </div>
                <span className="text-lg font-black tracking-tight whitespace-nowrap sm:text-xl">
                  SinhVien<span className="text-indigo-300">Market</span>
                </span>
              </Link>

              <form
                onSubmit={handleSearchSubmit}
                className="hidden w-36 shrink-0 xl:flex 2xl:w-60"
              >
                <div className="flex w-full overflow-hidden rounded-full border border-white/15 bg-white/12 backdrop-blur-md transition focus-within:border-indigo-400 focus-within:bg-white/18">
                  <input
                    type="text"
                    value={searchText}
                    onChange={(e) => setSearchText(e.target.value)}
                    placeholder="Tìm sản phẩm..."
                    className="min-w-0 flex-1 bg-transparent px-3 py-1.5 text-xs text-white placeholder:text-white/60 focus:outline-none focus-visible:outline-none 2xl:text-sm"
                  />
                  <button
                    type="submit"
                    aria-label="Tìm kiếm"
                    className="flex shrink-0 cursor-pointer items-center justify-center bg-white/15 px-2.5 text-white transition hover:bg-white/25"
                  >
                    <Search className="h-3.5 w-3.5" />
                  </button>
                </div>
              </form>

              <div className="hidden items-center gap-0.5 xl:flex 2xl:gap-1">
                <Link
                  href="/"
                  className={cn(
                    "inline-flex shrink-0 items-center gap-1 rounded-xl px-2 py-1.5 text-[13px] font-semibold whitespace-nowrap transition 2xl:gap-1.5 2xl:px-2.5 2xl:py-2 2xl:text-sm",
                    pathname === "/"
                      ? "bg-white/15 text-white"
                      : "text-white/80 hover:bg-white/10 hover:text-white",
                  )}
                >
                  <Home className="h-3.5 w-3.5 shrink-0 2xl:h-4 2xl:w-4" />
                  Trang chủ
                </Link>

                <Link
                  href="/products"
                  className={cn(
                    "inline-flex shrink-0 items-center gap-1 rounded-xl px-2 py-1.5 text-[13px] font-semibold whitespace-nowrap transition 2xl:gap-1.5 2xl:px-2.5 2xl:py-2 2xl:text-sm",
                    pathname.startsWith("/products") &&
                      pathname !== "/products/create"
                      ? "bg-white/15 text-white"
                      : "text-white/80 hover:bg-white/10 hover:text-white",
                  )}
                >
                  <Grid className="h-3.5 w-3.5 shrink-0 2xl:h-4 2xl:w-4" />
                  Sản phẩm
                </Link>

                <div ref={matchingMenuRef} className="relative shrink-0">
                  <button
                    type="button"
                    data-testid="nav-matching-trigger"
                    onClick={() => {
                      setMatchingMenuOpen((prev) => !prev);
                      setKtxMenuOpen(false);
                    }}
                    className={cn(
                      "inline-flex cursor-pointer items-center gap-1 rounded-xl px-2 py-1.5 text-[13px] font-semibold whitespace-nowrap transition 2xl:gap-1.5 2xl:px-2.5 2xl:py-2 2xl:text-sm",
                      pathname.startsWith("/requests") ||
                        pathname.startsWith("/marketplace/matching")
                        ? "bg-indigo-500/30 text-white ring-1 ring-indigo-300/40"
                        : "text-indigo-200 hover:bg-white/10 hover:text-white",
                    )}
                  >
                    <Sparkles className="h-3.5 w-3.5 shrink-0 text-fuchsia-300 2xl:h-4 2xl:w-4" />
                    Ghép nối
                    <ChevronDown
                      className={cn(
                        "h-3 w-3 shrink-0 opacity-75 transition-transform",
                        matchingMenuOpen && "rotate-180",
                      )}
                    />
                  </button>

                  {matchingMenuOpen && (
                    <div className="absolute left-0 mt-2 w-80 overflow-hidden rounded-2xl border border-border bg-surface p-2 text-fg shadow-2xl">
                      <div className="px-3 py-1.5 text-[11px] font-extrabold tracking-wider text-muted uppercase">
                        Chọn công cụ ghép nối
                      </div>
                      <Link
                        href="/requests"
                        onClick={() => setMatchingMenuOpen(false)}
                        className={cn(
                          "flex items-start gap-3 rounded-xl p-3 transition hover:bg-surface-2",
                          pathname.startsWith("/requests") &&
                            "bg-indigo-500/10 ring-1 ring-indigo-500/30",
                        )}
                      >
                        <div className="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-linear-to-br from-indigo-500 to-fuchsia-500 text-white shadow-sm">
                          <Sparkles className="h-4 w-4" />
                        </div>
                        <div className="min-w-0 flex-1">
                          <div className="flex items-center gap-1.5">
                            <span className="text-sm font-extrabold text-fg">
                              Ghép nối AI (Cần mua)
                            </span>
                            <span className="rounded-full bg-fuchsia-500/15 px-2 py-0.5 text-[10px] font-bold text-fuchsia-600 dark:text-fuchsia-300">
                              Đa phương thức
                            </span>
                          </div>
                          <p className="mt-0.5 text-xs leading-relaxed text-muted">
                            Đăng tin cần mua bằng văn bản & ảnh mẫu, tự động gợi ý sản phẩm khớp tiêu chí.
                          </p>
                        </div>
                      </Link>

                      <Link
                        href="/marketplace/matching"
                        onClick={() => setMatchingMenuOpen(false)}
                        className={cn(
                          "mt-1 flex items-start gap-3 rounded-xl p-3 transition hover:bg-surface-2",
                          pathname.startsWith("/marketplace/matching") &&
                            "bg-indigo-500/10 ring-1 ring-indigo-500/30",
                        )}
                      >
                        <div className="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-indigo-500/15 text-indigo-600 dark:text-indigo-300">
                          <Search className="h-4 w-4" />
                        </div>
                        <div className="min-w-0 flex-1">
                          <span className="text-sm font-extrabold text-fg">
                            Tìm sản phẩm phù hợp
                          </span>
                          <p className="mt-0.5 text-xs leading-relaxed text-muted">
                            Tra cứu nhanh các tin đang bán theo mô tả, danh mục, tình trạng và khoảng giá.
                          </p>
                        </div>
                      </Link>
                    </div>
                  )}
                </div>

                <div ref={ktxMenuRef} className="relative shrink-0">
                  <button
                    type="button"
                    data-testid="nav-ktx-trigger"
                    onClick={() => {
                      setKtxMenuOpen((prev) => !prev);
                      setMatchingMenuOpen(false);
                    }}
                    className={cn(
                      "inline-flex cursor-pointer items-center gap-1 rounded-xl px-2 py-1.5 text-[13px] font-semibold whitespace-nowrap transition 2xl:gap-1.5 2xl:px-2.5 2xl:py-2 2xl:text-sm",
                      pathname.startsWith("/residence") ||
                        pathname.startsWith("/rentals")
                        ? "bg-white/15 text-white"
                        : "text-white/80 hover:bg-white/10 hover:text-white",
                    )}
                  >
                    <ClipboardList className="h-3.5 w-3.5 shrink-0 text-cyan-300 2xl:h-4 2xl:w-4" />
                    Ký túc xá
                    <ChevronDown
                      className={cn(
                        "h-3 w-3 shrink-0 opacity-75 transition-transform",
                        ktxMenuOpen && "rotate-180",
                      )}
                    />
                  </button>

                  {ktxMenuOpen && (
                    <div className="absolute left-0 mt-2 w-72 overflow-hidden rounded-2xl border border-border bg-surface p-2 text-fg shadow-2xl">
                      <div className="px-3 py-1.5 text-[11px] font-extrabold tracking-wider text-muted uppercase">
                        Dịch vụ nội trú KTX
                      </div>
                      {[
                        {
                          href: "/residence",
                          label: "Hồ sơ & Hợp đồng lưu trú",
                          desc: "Đăng ký phòng KTX & theo dõi hợp đồng",
                        },
                        {
                          href: "/residence/announcements",
                          label: "Thông báo & Nội quy",
                          desc: "Bản tin, sự kiện và quy định KTX",
                        },
                        {
                          href: "/residence/finance",
                          label: "Hóa đơn & Phí lưu trú",
                          desc: "Tra cứu công nợ và gửi xác nhận đóng phí",
                        },
                        {
                          href: "/residence/services",
                          label: "Yêu cầu sửa chữa & Hỗ trợ",
                          desc: "Báo hỏng điện nước, dịch vụ phòng ở",
                        },
                        {
                          href: "/residence/assistant",
                          label: "Trợ lý Nội quy KTX",
                          desc: "Hỏi đáp nhanh quy định giờ giấc, sinh hoạt",
                        },
                        {
                          href: "/rentals",
                          label: "Mặt bằng cho thuê KTX",
                          desc: "Ki-ốt dịch vụ sinh viên trong khuôn viên",
                        },
                      ].map((item) => (
                        <Link
                          key={item.href}
                          href={item.href}
                          onClick={() => setKtxMenuOpen(false)}
                          className={cn(
                            "block rounded-xl px-3 py-2 transition hover:bg-surface-2",
                            pathname === item.href &&
                              "bg-indigo-500/10 font-bold text-primary",
                          )}
                        >
                          <div className="text-sm font-bold text-fg">
                            {item.label}
                          </div>
                          <div className="text-xs text-muted">{item.desc}</div>
                        </Link>
                      ))}
                    </div>
                  )}
                </div>

                <Link
                  href="/#giveaway"
                  className="inline-flex shrink-0 items-center gap-1 rounded-xl px-2 py-1.5 text-[13px] font-semibold whitespace-nowrap text-amber-300/90 transition hover:bg-white/10 hover:text-amber-200 2xl:gap-1.5 2xl:px-2.5 2xl:py-2 2xl:text-sm"
                >
                  <Gift className="h-3.5 w-3.5 shrink-0 2xl:h-4 2xl:w-4" />
                  Giveaway
                </Link>

                {user ? (
                  <>
                    {isAdmin ? (
                      <Link
                        href="/admin"
                        className="ml-0.5 inline-flex shrink-0 items-center gap-1 rounded-full border border-rose-400/40 bg-rose-500/20 px-2.5 py-1.5 text-xs font-extrabold whitespace-nowrap text-rose-200 shadow-xs transition hover:bg-rose-500/30"
                      >
                        <ShieldCheck className="h-3.5 w-3.5 shrink-0 text-rose-300" />
                        Admin Panel
                      </Link>
                    ) : (
                      <Link
                        href="/products/create"
                        aria-label="Đăng sản phẩm"
                        className="inline-flex shrink-0 items-center gap-1 rounded-xl bg-white/10 px-2.5 py-1.5 text-[13px] font-semibold whitespace-nowrap text-white transition hover:bg-white/18 2xl:gap-1.5 2xl:px-3 2xl:py-2 2xl:text-sm"
                      >
                        <PlusCircle className="h-3.5 w-3.5 shrink-0 text-indigo-300 2xl:h-4 2xl:w-4" />
                        Đăng bán
                      </Link>
                    )}

                    <div ref={notifMenuRef} className="relative">
                      <button
                        type="button"
                        onClick={() => setNotifOpen((prev) => !prev)}
                        aria-label="Thông báo"
                        className="relative flex h-9 w-9 cursor-pointer items-center justify-center rounded-xl text-white/80 transition hover:bg-white/10 hover:text-white"
                      >
                        <Bell className="h-4 w-4" />
                        <span className="absolute top-1.5 right-1.5 h-2 w-2 rounded-full bg-rose-500" />
                      </button>

                      {notifOpen && (
                        <div className="absolute right-0 mt-2 w-80 overflow-hidden rounded-2xl border border-border bg-surface text-fg shadow-2xl">
                          <div className="border-b border-border px-4 py-2.5 text-xs font-extrabold tracking-wider text-muted uppercase">
                            Thông báo hệ thống
                          </div>
                          <div className="divide-y divide-border text-xs">
                            <div className="px-4 py-3">
                              <p className="font-bold text-fg">
                                🎉 Chào mừng đến với SinhVienMarket!
                              </p>
                              <p className="mt-0.5 text-muted">
                                Hệ thống chợ sinh viên KTX đã liên kết đồng bộ cả cổng Sinh Viên và Admin Panel.
                              </p>
                            </div>
                            <div className="px-4 py-3">
                              <p className="font-bold text-fg">
                                🎁 Sự kiện Giveaway đang mở
                              </p>
                              <p className="mt-0.5 text-muted">
                                Tham gia bốc thăm trúng thưởng miễn phí ngay tại trang chủ.
                              </p>
                            </div>
                          </div>
                          <Link
                            href="/notifications"
                            onClick={() => setNotifOpen(false)}
                            className="block border-t border-border px-4 py-3 text-center text-xs font-bold text-primary hover:bg-surface-2"
                          >
                            Xem tất cả thông báo
                          </Link>
                        </div>
                      )}
                    </div>

                    <ThemeToggle />

                    <div ref={userMenuRef} className="relative ml-0.5 shrink-0">
                      <button
                        type="button"
                        onClick={() => setUserMenuOpen((prev) => !prev)}
                        className="flex cursor-pointer items-center gap-1.5 rounded-xl bg-white/10 px-2 py-1.5 text-xs font-semibold whitespace-nowrap text-white transition hover:bg-white/18 2xl:gap-2 2xl:px-2.5 2xl:text-sm"
                      >
                        <span className="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-linear-to-br from-indigo-500 to-pink-500 text-xs font-extrabold text-white 2xl:h-7 2xl:w-7">
                          {avatarLetter}
                        </span>
                        <span className="max-w-[110px] truncate 2xl:max-w-[160px]">
                          {user.name}
                        </span>
                        <ChevronDown className="h-3 w-3 shrink-0 opacity-75" />
                      </button>

                      {userMenuOpen && (
                        <div className="absolute right-0 mt-2 max-h-[calc(100vh-5rem)] w-60 overflow-y-auto rounded-2xl border border-border bg-surface py-2 text-fg shadow-2xl">
                          <div className="border-b border-border px-4 py-2.5">
                            <p className="truncate text-sm font-extrabold text-fg">
                              {user.name}
                            </p>
                            <p className="truncate text-xs text-muted">
                              {user.email}
                            </p>
                            <span
                              className={cn(
                                "mt-1.5 inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-[11px] font-bold",
                                isAdmin
                                  ? "bg-rose-500/15 text-rose-500"
                                  : "bg-indigo-500/15 text-indigo-500",
                              )}
                            >
                              {isAdmin ? (
                                <>
                                  <ShieldCheck className="h-3 w-3" />
                                  Quản trị viên
                                </>
                              ) : (
                                <>
                                  <UserCheck className="h-3 w-3" />
                                  Sinh viên đã xác thực
                                </>
                              )}
                            </span>
                          </div>

                          <div className="py-1">
                            {isAdmin ? (
                              <>
                                <Link
                                  href="/admin"
                                  onClick={() => setUserMenuOpen(false)}
                                  className="flex items-center gap-2.5 px-4 py-2.5 text-sm font-bold text-indigo-600 transition hover:bg-surface-2 dark:text-indigo-400"
                                >
                                  <ShieldCheck className="h-4 w-4" />
                                  Vào trang Quản trị (/admin)
                                </Link>
                                <Link
                                  href="/admin/residence"
                                  onClick={() => setUserMenuOpen(false)}
                                  className="flex items-center gap-2.5 px-4 py-2 text-sm font-semibold text-fg transition hover:bg-surface-2"
                                >
                                  <ClipboardList className="h-4 w-4 text-indigo-500" />
                                  Xét duyệt KTX
                                </Link>
                              </>
                            ) : (
                              <>
                                <Link
                                  href="/residence"
                                  onClick={() => setUserMenuOpen(false)}
                                  className="flex items-center gap-2.5 px-4 py-2 text-sm font-semibold text-fg transition hover:bg-surface-2"
                                >
                                  <Home className="h-4 w-4 text-indigo-500" />
                                  Hồ sơ lưu trú
                                </Link>
                                {user.roles?.includes("dorm_manager") && (
                                  <Link
                                    href="/admin/residence"
                                    onClick={() => setUserMenuOpen(false)}
                                    className="flex items-center gap-2.5 px-4 py-2 text-sm font-semibold text-fg transition hover:bg-surface-2"
                                  >
                                    <ClipboardList className="h-4 w-4 text-indigo-500" />
                                    Xét duyệt KTX
                                  </Link>
                                )}
                                {user.roles?.includes("service_staff") && (
                                  <Link
                                    href="/residence/services"
                                    onClick={() => setUserMenuOpen(false)}
                                    className="flex items-center gap-2.5 px-4 py-2 text-sm font-semibold text-fg transition hover:bg-surface-2"
                                  >
                                    <ClipboardList className="h-4 w-4 text-indigo-500" />
                                    Xử lý yêu cầu KTX
                                  </Link>
                                )}
                                <Link
                                  href="/dashboard"
                                  onClick={() => setUserMenuOpen(false)}
                                  className="flex items-center gap-2.5 px-4 py-2 text-sm font-semibold text-fg transition hover:bg-surface-2"
                                >
                                  <Grid className="h-4 w-4 text-indigo-500" />
                                  Bảng điều khiển
                                </Link>
                                <Link
                                  href="/profile"
                                  onClick={() => setUserMenuOpen(false)}
                                  className="flex items-center gap-2.5 px-4 py-2 text-sm font-semibold text-fg transition hover:bg-surface-2"
                                >
                                  <UserCheck className="h-4 w-4 text-indigo-500" />
                                  Hồ sơ cá nhân
                                </Link>
                                <Link
                                  href="/my-listings"
                                  onClick={() => setUserMenuOpen(false)}
                                  className="flex items-center gap-2.5 px-4 py-2 text-sm font-semibold text-fg transition hover:bg-surface-2"
                                >
                                  <PlusCircle className="h-4 w-4 text-indigo-500" />
                                  Tin đăng của tôi
                                </Link>
                                <Link
                                  href="/transactions"
                                  onClick={() => setUserMenuOpen(false)}
                                  className="flex items-center gap-2.5 px-4 py-2 text-sm font-semibold text-fg transition hover:bg-surface-2"
                                >
                                  <ClipboardList className="h-4 w-4 text-indigo-500" />
                                  Giao dịch
                                </Link>
                                <Link
                                  href="/wishlist"
                                  onClick={() => setUserMenuOpen(false)}
                                  className="flex items-center gap-2.5 px-4 py-2 text-sm font-semibold text-fg transition hover:bg-surface-2"
                                >
                                  <Heart className="h-4 w-4 text-rose-500" />
                                  Yêu thích
                                </Link>
                                <Link
                                  href="/chat"
                                  onClick={() => setUserMenuOpen(false)}
                                  className="flex items-center gap-2.5 px-4 py-2 text-sm font-semibold text-fg transition hover:bg-surface-2"
                                >
                                  <MessageCircle className="h-4 w-4 text-indigo-500" />
                                  Tin nhắn
                                </Link>
                                <Link
                                  href="/notifications"
                                  onClick={() => setUserMenuOpen(false)}
                                  className="flex items-center gap-2.5 px-4 py-2 text-sm font-semibold text-fg transition hover:bg-surface-2"
                                >
                                  <Bell className="h-4 w-4 text-amber-500" />
                                  Thông báo
                                </Link>
                                <Link
                                  href="/rewards"
                                  onClick={() => setUserMenuOpen(false)}
                                  className="flex items-center gap-2.5 px-4 py-2 text-sm font-semibold text-fg transition hover:bg-surface-2"
                                >
                                  <Coins className="h-4 w-4 text-amber-500" />
                                  Xu & điểm thưởng
                                </Link>
                                <Link
                                  href="/products/create"
                                  onClick={() => setUserMenuOpen(false)}
                                  className="flex items-center gap-2.5 px-4 py-2 text-sm font-semibold text-fg transition hover:bg-surface-2"
                                >
                                  <PlusCircle className="h-4 w-4 text-indigo-500" />
                                  Đăng bán
                                </Link>
                                <Link
                                  href="/products"
                                  onClick={() => setUserMenuOpen(false)}
                                  className="flex items-center gap-2.5 px-4 py-2 text-sm font-semibold text-fg transition hover:bg-surface-2"
                                >
                                  <ShoppingBag className="h-4 w-4 text-indigo-500" />
                                  Khám phá sản phẩm
                                </Link>
                                <Link
                                  href="/#giveaway"
                                  onClick={() => setUserMenuOpen(false)}
                                  className="flex items-center gap-2.5 px-4 py-2 text-sm font-semibold text-amber-600 transition hover:bg-surface-2 dark:text-amber-400"
                                >
                                  <Gift className="h-4 w-4" />
                                  Sự kiện Giveaway
                                </Link>
                              </>
                            )}

                            <Link
                              href="/login-role"
                              onClick={() => setUserMenuOpen(false)}
                              className="flex items-center gap-2.5 px-4 py-2 text-sm font-semibold text-fg transition hover:bg-surface-2"
                            >
                              <Sparkles className="h-4 w-4 text-purple-500" />
                              Chuyển cổng đăng nhập
                            </Link>
                          </div>

                          <div className="border-t border-border pt-1">
                            <button
                              type="button"
                              disabled={loggingOut}
                              onClick={handleLogout}
                              className="flex w-full cursor-pointer items-center gap-2.5 px-4 py-2.5 text-left text-sm font-bold text-rose-600 transition hover:bg-rose-50 disabled:opacity-50 dark:text-rose-400 dark:hover:bg-rose-500/10"
                            >
                              <LogOut className="h-4 w-4" />
                              {loggingOut ? "Đang đăng xuất..." : "Đăng xuất"}
                            </button>
                          </div>
                        </div>
                      )}
                    </div>
                  </>
                ) : (
                  <div className="ml-2 flex shrink-0 items-center gap-2">
                    <ThemeToggle />

                    <Link
                      href="/login-role"
                      className="inline-flex shrink-0 items-center gap-1.5 rounded-xl px-3 py-2 text-sm font-semibold whitespace-nowrap text-white/90 transition hover:bg-white/10 hover:text-white"
                    >
                      <LogIn className="h-4 w-4 shrink-0" />
                      Đăng nhập
                    </Link>

                    <Link
                      href="/register"
                      className="inline-flex shrink-0 items-center gap-1.5 rounded-full bg-linear-to-r from-indigo-500 to-purple-500 px-3.5 py-2 text-sm font-bold whitespace-nowrap text-white shadow-[0_4px_14px_rgba(99,102,241,0.45)] transition hover:-translate-y-0.5 hover:brightness-110"
                    >
                      <UserPlus className="h-4 w-4 shrink-0" />
                      Đăng ký
                    </Link>
                  </div>
                )}
              </div>

              <div className="flex items-center gap-2 xl:hidden">
                <ThemeToggle />
                <button
                  type="button"
                  onClick={() => setMobileOpen((prev) => !prev)}
                  aria-label="Mở menu"
                  className="rounded-xl p-2 text-white hover:bg-white/10"
                >
                  {mobileOpen ? (
                    <X className="h-6 w-6" />
                  ) : (
                    <Menu className="h-6 w-6" />
                  )}
                </button>
              </div>
            </div>

            {mobileOpen && (
              <div className="space-y-3 border-t border-white/10 pt-3 pb-4 xl:hidden">
                <form onSubmit={handleSearchSubmit}>
                  <div className="flex overflow-hidden rounded-full border border-white/15 bg-white/12">
                    <input
                      type="text"
                      value={searchText}
                      onChange={(e) => setSearchText(e.target.value)}
                      placeholder="Tìm sản phẩm..."
                      className="flex-1 bg-transparent px-4 py-2 text-sm text-white placeholder:text-white/60 focus:outline-none"
                    />
                    <button
                      type="submit"
                      className="bg-white/20 px-4 text-white"
                    >
                      <Search className="h-4 w-4" />
                    </button>
                  </div>
                </form>

                <div className="flex flex-col gap-1">
                  <Link
                    href="/"
                    onClick={() => setMobileOpen(false)}
                    className="flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-semibold text-white/90 hover:bg-white/10"
                  >
                    <Home className="h-4 w-4" />
                    Trang chủ
                  </Link>
                  <Link
                    href="/products"
                    onClick={() => setMobileOpen(false)}
                    className="flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-semibold text-white/90 hover:bg-white/10"
                  >
                    <Grid className="h-4 w-4" />
                    Sản phẩm
                  </Link>
                  <Link
                    href="/marketplace/matching"
                    onClick={() => setMobileOpen(false)}
                    className="flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-semibold text-white/90 hover:bg-white/10"
                  >
                    <Sparkles className="h-4 w-4" />
                    Ghép nối sản phẩm
                  </Link>
                  <Link
                    href="/requests"
                    onClick={() => setMobileOpen(false)}
                    className="flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-semibold text-indigo-200 hover:bg-white/10"
                  >
                    <Sparkles className="h-4 w-4 text-fuchsia-300" />
                    Ghép nối AI (Request–Offer)
                  </Link>
                  <Link
                    href="/#giveaway"
                    onClick={() => setMobileOpen(false)}
                    className="flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-semibold text-amber-300 hover:bg-white/10"
                  >
                    <Gift className="h-4 w-4" />
                    Sự kiện Giveaway
                  </Link>

                  {user ? (
                    <>
                      {isAdmin && (
                        <>
                          <Link
                            href="/admin"
                            onClick={() => setMobileOpen(false)}
                            className="flex items-center gap-2 rounded-xl bg-rose-500/20 px-3 py-2.5 text-sm font-bold text-rose-200"
                          >
                            <ShieldCheck className="h-4 w-4" />
                            Vào Admin Panel (/admin)
                          </Link>
                          <Link
                            href="/admin/residence"
                            onClick={() => setMobileOpen(false)}
                            className="flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-semibold text-white/90 hover:bg-white/10"
                          >
                            <ClipboardList className="h-4 w-4" />
                            Xét duyệt KTX
                          </Link>
                        </>
                      )}
                      {!isAdmin && (
                        <>
                          <Link
                            href="/residence"
                            onClick={() => setMobileOpen(false)}
                            className="flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-semibold text-white/90 hover:bg-white/10"
                          >
                            <Home className="h-4 w-4" />
                            Hồ sơ lưu trú
                          </Link>
                          {user.roles?.includes("dorm_manager") && (
                            <Link
                              href="/admin/residence"
                              onClick={() => setMobileOpen(false)}
                              className="flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-semibold text-white/90 hover:bg-white/10"
                            >
                              <ClipboardList className="h-4 w-4" />
                              Xét duyệt KTX
                            </Link>
                          )}
                          {user.roles?.includes("service_staff") && (
                            <Link
                              href="/residence/services"
                              onClick={() => setMobileOpen(false)}
                              className="flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-semibold text-white/90 hover:bg-white/10"
                            >
                              <ClipboardList className="h-4 w-4" />
                              Xử lý yêu cầu KTX
                            </Link>
                          )}
                          <Link
                            href="/dashboard"
                            onClick={() => setMobileOpen(false)}
                            className="flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-semibold text-white/90 hover:bg-white/10"
                          >
                            <Grid className="h-4 w-4" />
                            Bảng điều khiển
                          </Link>
                          <Link
                            href="/profile"
                            onClick={() => setMobileOpen(false)}
                            className="flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-semibold text-white/90 hover:bg-white/10"
                          >
                            <UserCheck className="h-4 w-4" />
                            Hồ sơ cá nhân
                          </Link>
                          <Link
                            href="/my-listings"
                            onClick={() => setMobileOpen(false)}
                            className="flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-semibold text-white/90 hover:bg-white/10"
                          >
                            <PlusCircle className="h-4 w-4" />
                            Tin đăng của tôi
                          </Link>
                          <Link
                            href="/transactions"
                            onClick={() => setMobileOpen(false)}
                            className="flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-semibold text-white/90 hover:bg-white/10"
                          >
                            <ClipboardList className="h-4 w-4" />
                            Giao dịch
                          </Link>
                          <Link
                            href="/wishlist"
                            onClick={() => setMobileOpen(false)}
                            className="flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-semibold text-white/90 hover:bg-white/10"
                          >
                            <Heart className="h-4 w-4" />
                            Yêu thích
                          </Link>
                          <Link
                            href="/chat"
                            onClick={() => setMobileOpen(false)}
                            className="flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-semibold text-white/90 hover:bg-white/10"
                          >
                            <MessageCircle className="h-4 w-4" />
                            Tin nhắn
                          </Link>
                          <Link
                            href="/notifications"
                            onClick={() => setMobileOpen(false)}
                            className="flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-semibold text-white/90 hover:bg-white/10"
                          >
                            <Bell className="h-4 w-4" />
                            Thông báo
                          </Link>
                          <Link
                            href="/rewards"
                            onClick={() => setMobileOpen(false)}
                            className="flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-semibold text-white/90 hover:bg-white/10"
                          >
                            <Coins className="h-4 w-4" />
                            Xu & điểm thưởng
                          </Link>
                        </>
                      )}
                      <button
                        type="button"
                        onClick={handleLogout}
                        className="flex items-center gap-2 rounded-xl px-3 py-2.5 text-left text-sm font-bold text-rose-300 hover:bg-white/10"
                      >
                        <LogOut className="h-4 w-4" />
                        Đăng xuất ({user.name})
                      </button>
                    </>
                  ) : (
                    <>
                      <Link
                        href="/login-role"
                        onClick={() => setMobileOpen(false)}
                        className="flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-semibold text-white/90 hover:bg-white/10"
                      >
                        <LogIn className="h-4 w-4" />
                        Đăng nhập (Sinh viên / Admin)
                      </Link>
                      <Link
                        href="/register"
                        onClick={() => setMobileOpen(false)}
                        className="flex items-center gap-2 rounded-xl bg-indigo-600 px-3 py-2.5 text-sm font-bold text-white"
                      >
                        <UserPlus className="h-4 w-4" />
                        Đăng ký tài khoản
                      </Link>
                    </>
                  )}
                </div>
              </div>
            )}
          </div>
        </nav>

        <div className="mx-auto w-full max-w-7xl px-4 pt-3">
          <FlashBanner />
        </div>

        <main className="flex-1">
          <div key={pathname} className="page-enter">
            {children}
          </div>
        </main>

        <footer className="mt-16 border-t border-white/10 bg-[#0f0c29] py-12 text-white/75">
          <div className="mx-auto max-w-7xl px-4">
            <div className="grid grid-cols-1 gap-8 md:grid-cols-12">
              <div className="md:col-span-5">
                <div className="flex items-center gap-2 text-lg font-black text-white">
                  <ShoppingBag className="h-5 w-5 text-indigo-400" />
                  SinhVien<span className="text-indigo-300">Market</span>
                </div>
                <p className="mt-3 max-w-sm text-sm leading-relaxed text-white/65">
                  Nền tảng mua bán, trao đổi & đấu giá ngược dành riêng cho sinh viên{" "}
                  <strong className="text-white/90">KTX Đại học Quốc gia</strong>.
                  Tiết kiệm chi phí, kết nối cộng đồng sinh viên.
                </p>
              </div>

              <div className="md:col-span-3">
                <h3 className="mb-3 text-xs font-extrabold tracking-wider text-white uppercase">
                  Khám phá
                </h3>
                <ul className="space-y-2 text-sm">
                  <li>
                    <Link
                      href="/products"
                      className="text-white/65 transition hover:text-white"
                    >
                      Tất cả sản phẩm
                    </Link>
                  </li>
                  <li>
                    <Link
                      href="/products?type=auction"
                      className="text-white/65 transition hover:text-white"
                    >
                      Đấu giá ngược
                    </Link>
                  </li>
                  <li>
                    <Link
                      href="/products?type=exchange"
                      className="text-white/65 transition hover:text-white"
                    >
                      Trao đổi đồ dùng
                    </Link>
                  </li>
                  <li>
                    <Link
                      href="/leaderboard"
                      className="text-white/65 transition hover:text-white"
                    >
                      Bảng xếp hạng người bán
                    </Link>
                  </li>
                </ul>
              </div>

              <div className="md:col-span-4">
                <h3 className="mb-3 text-xs font-extrabold tracking-wider text-white uppercase">
                  Cổng truy cập nhanh
                </h3>
                <ul className="space-y-2 text-sm">
                  <li>
                    <Link
                      href="/login-role"
                      onClick={(event) => {
                        event.preventDefault();
                        void handleQuickAccessClick({ href: "/login-role" });
                      }}
                      className="text-white/65 transition hover:text-white"
                    >
                      Chọn cổng đăng nhập (Sinh viên / Quản trị)
                    </Link>
                  </li>
                  <li>
                    <Link
                      href="/login"
                      onClick={(event) => {
                        event.preventDefault();
                        void handleQuickAccessClick({ href: "/login" });
                      }}
                      className="text-white/65 transition hover:text-white"
                    >
                      Đăng nhập Sinh viên
                    </Link>
                  </li>
                  <li>
                    <Link
                      href="/admin-login"
                      onClick={(event) => {
                        event.preventDefault();
                        void handleQuickAccessClick({ href: "/admin-login" });
                      }}
                      className="text-indigo-300 transition hover:text-indigo-200"
                    >
                      🛡️ Cổng Quản trị viên (Admin Panel)
                    </Link>
                  </li>
                </ul>
              </div>
            </div>

            <div className="mt-10 border-t border-white/10 pt-6 text-center text-xs text-white/45">
              © 2026 SinhVienMarket — Hệ thống Thương mại Điện tử Sinh viên (Next.js 16 + React 19 + Tailwind CSS 4)
            </div>
          </div>
        </footer>
      </div>
      {portalNotice && (
        <PortalAccessDialog
          title={portalNotice.title}
          description={portalNotice.description}
          confirmLabel={portalNotice.confirmLabel}
          busy={loggingOut}
          onClose={() => setPortalNotice(null)}
          onConfirm={
            portalNotice.intent === "switch-account" ||
            portalNotice.intent === "open-admin"
              ? handlePortalNoticeConfirm
              : undefined
          }
        />
      )}
    </MainShellContext.Provider>
  );
}
