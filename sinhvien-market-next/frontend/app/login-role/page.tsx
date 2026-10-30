"use client";

import Link from "next/link";
import {
  ArrowLeft,
  ArrowRight,
  GraduationCap,
  Lock,
  ShieldCheck,
  Store,
} from "lucide-react";
import { FlashBanner } from "@/components/auth/FlashBanner";
import { useGuestRedirect } from "@/lib/use-guest-redirect";

export default function LoginRolePage() {
  useGuestRedirect();

  return (
    <div
      className="relative flex min-h-screen items-center justify-center overflow-hidden px-4 py-10 text-white sm:px-6"
      style={{
        background:
          "linear-gradient(135deg, #0f0c29 0%, #302b63 50%, #1a055c 100%)",
      }}
    >
      <div
        aria-hidden="true"
        className="pointer-events-none absolute -top-24 -left-24 h-[400px] w-[400px] rounded-full opacity-50 blur-[80px] animate-float-orb"
        style={{ background: "#6366f1" }}
      />
      <div
        aria-hidden="true"
        className="pointer-events-none absolute -right-24 -bottom-36 h-[500px] w-[500px] rounded-full opacity-50 blur-[80px] animate-float-orb"
        style={{
          background: "#ec4899",
          animationDirection: "alternate-reverse",
          animationDuration: "11s",
        }}
      />
      <div
        aria-hidden="true"
        className="pointer-events-none absolute top-[40%] left-[40%] h-[300px] w-[300px] rounded-full opacity-45 blur-[80px] animate-float-orb"
        style={{
          background: "#8b5cf6",
          animationDuration: "13s",
        }}
      />

      <main className="relative z-10 w-full max-w-[900px] animate-slide-up">
        <header className="mb-10 text-center sm:mb-12">
          <h1
            className="inline-flex flex-wrap items-center justify-center gap-3 text-3xl font-black tracking-tight sm:text-5xl"
            style={{
              background:
                "linear-gradient(135deg, #ffffff 0%, #a5b4fc 50%, #f472b6 100%)",
              WebkitBackgroundClip: "text",
              WebkitTextFillColor: "transparent",
            }}
          >
            <Store
              className="h-9 w-9 shrink-0 text-indigo-400 sm:h-11 sm:w-11"
              aria-hidden="true"
            />
            <span>SinhVienMarket</span>
          </h1>
          <p className="mt-3 text-base font-normal text-white/75 sm:text-lg">
            Vui lòng chọn cổng đăng nhập phù hợp với tài khoản của bạn
          </p>
        </header>

        <div className="mx-auto mb-6 max-w-md">
          <FlashBanner />
        </div>

        <div className="grid grid-cols-1 gap-6 md:grid-cols-2 md:gap-8">
          <Link
            href="/login"
            className="group relative flex flex-col items-center overflow-hidden rounded-[28px] border border-white/12 bg-white/[0.06] px-7 py-10 text-center backdrop-blur-2xl transition-all duration-300 hover:-translate-y-2.5 hover:border-indigo-500/50 hover:bg-white/[0.1] hover:shadow-[0_25px_50px_rgba(99,102,241,0.28)] sm:px-9 sm:py-12"
          >
            <div
              className="mb-6 flex h-[84px] w-[84px] items-center justify-center rounded-[24px] border border-indigo-500/30 bg-indigo-500/15 text-indigo-400 transition-transform duration-300 group-hover:scale-110 group-hover:rotate-3 group-hover:bg-indigo-500 group-hover:text-white group-hover:shadow-[0_10px_25px_rgba(99,102,241,0.5)]"
              aria-hidden="true"
            >
              <GraduationCap className="h-10 w-10" />
            </div>

            <h2 className="mb-3 text-2xl font-extrabold text-white">
              Tài khoản Sinh Viên
            </h2>
            <p className="mb-8 flex-grow text-sm leading-relaxed text-white/70 sm:text-[15px]">
              Dành cho sinh viên mua bán, trao đổi giáo trình, đồ dùng học tập
              và săn đồ cũ giá rẻ.
            </p>

            <span
              className="inline-flex w-full items-center justify-center gap-2 rounded-2xl px-6 py-3.5 text-base font-bold text-white shadow-[0_4px_15px_rgba(99,102,241,0.4)] transition-all duration-200 group-hover:shadow-[0_6px_22px_rgba(99,102,241,0.6)]"
              style={{
                background:
                  "linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%)",
              }}
            >
              <span>Đăng nhập Sinh Viên</span>
              <ArrowRight
                className="h-4 w-4 transition-transform duration-200 group-hover:translate-x-1"
                aria-hidden="true"
              />
            </span>
          </Link>

          <Link
            href="/admin-login"
            className="group relative flex flex-col items-center overflow-hidden rounded-[28px] border border-white/12 bg-white/[0.06] px-7 py-10 text-center backdrop-blur-2xl transition-all duration-300 hover:-translate-y-2.5 hover:border-pink-500/50 hover:bg-white/[0.1] hover:shadow-[0_25px_50px_rgba(236,72,153,0.28)] sm:px-9 sm:py-12"
          >
            <div
              className="mb-6 flex h-[84px] w-[84px] items-center justify-center rounded-[24px] border border-pink-500/30 bg-pink-500/15 text-pink-400 transition-transform duration-300 group-hover:scale-110 group-hover:-rotate-3 group-hover:bg-pink-500 group-hover:text-white group-hover:shadow-[0_10px_25px_rgba(236,72,153,0.5)]"
              aria-hidden="true"
            >
              <ShieldCheck className="h-10 w-10" />
            </div>

            <h2 className="mb-3 text-2xl font-extrabold text-white">
              Ban Quản Trị
            </h2>
            <p className="mb-8 flex-grow text-sm leading-relaxed text-white/70 sm:text-[15px]">
              Cổng nội bộ dành riêng cho Admin và Kiểm duyệt viên quản lý hệ
              thống chợ sinh viên.
            </p>

            <span
              className="inline-flex w-full items-center justify-center gap-2 rounded-2xl px-6 py-3.5 text-base font-bold text-white shadow-[0_4px_15px_rgba(236,72,153,0.4)] transition-all duration-200 group-hover:shadow-[0_6px_22px_rgba(236,72,153,0.6)]"
              style={{
                background:
                  "linear-gradient(135deg, #ec4899 0%, #f43f5e 100%)",
              }}
            >
              <span>Đăng nhập Quản Trị</span>
              <Lock
                className="h-4 w-4 transition-transform duration-200 group-hover:scale-110"
                aria-hidden="true"
              />
            </span>
          </Link>
        </div>

        <div className="mt-10 text-center">
          <Link
            href="/products"
            className="inline-flex items-center gap-2 text-sm font-medium text-white/60 transition-colors hover:text-white"
          >
            <ArrowLeft className="h-4 w-4" aria-hidden="true" />
            <span>Trở về trang chủ</span>
          </Link>
        </div>
      </main>
    </div>
  );
}
