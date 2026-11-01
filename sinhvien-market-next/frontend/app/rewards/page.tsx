"use client";

import { useCallback, useEffect, useState } from "react";
import Link from "next/link";
import {
  CalendarCheck,
  Coins,
  Flame,
  LoaderCircle,
  Sparkles,
} from "lucide-react";
import { useMainShell } from "@/components/layout/MainShell";
import {
  FlowAlert,
  UserFlowApp,
  UserFlowLayout,
} from "@/components/flows/FlowFrame";
import { Button } from "@/components/ui/Button";
import {
  checkIn,
  getRewards,
  type RewardsSummary,
} from "@/lib/api/user-flows";

function RewardsContent() {
  const { user, loadingUser } = useMainShell();
  const [rewards, setRewards] = useState<RewardsSummary | null>(null);
  const [loading, setLoading] = useState(true);
  const [checkingIn, setCheckingIn] = useState(false);
  const [error, setError] = useState("");
  const [notice, setNotice] = useState("");

  const refresh = useCallback(async () => {
    if (!user) return;
    setLoading(true);
    try {
      setRewards(await getRewards(user));
      setError("");
    } catch (cause) {
      setError(cause instanceof Error ? cause.message : "Không tải được số dư xu.");
    } finally {
      setLoading(false);
    }
  }, [user]);

  useEffect(() => {
    if (loadingUser || !user) return;
    void Promise.resolve().then(refresh);
  }, [loadingUser, refresh, user]);

  async function onCheckin() {
    if (!user) return;
    setCheckingIn(true);
    setError("");
    setNotice("");
    try {
      const updated = await checkIn(user);
      setRewards(updated);
      setNotice("Điểm danh thành công! Xu đã được cộng vào tài khoản.");
    } catch (cause) {
      setError(cause instanceof Error ? cause.message : "Điểm danh thất bại.");
    } finally {
      setCheckingIn(false);
    }
  }

  return (
    <UserFlowLayout
      title="Xu & điểm thưởng"
      description="Điểm danh mỗi ngày và dùng xu để làm mới tin đăng."
    >
      <FlowAlert error={error} success={notice} />
      {loading || !rewards ? (
        <div className="rounded-2xl border border-border bg-surface py-16 text-center text-muted">
          <LoaderCircle className="mx-auto mb-2 h-6 w-6 animate-spin" />
          Đang tải số dư...
        </div>
      ) : (
        <div className="grid gap-5 lg:grid-cols-[1.2fr_0.8fr]">
          <section className="rounded-3xl bg-linear-to-br from-amber-400 via-orange-500 to-rose-500 p-6 text-white shadow-lg sm:p-8">
            <div className="flex items-start justify-between gap-4">
              <div>
                <p className="text-sm font-bold text-white/80">Số dư hiện tại</p>
                <p className="mt-2 flex items-center gap-2 text-4xl font-black">
                  <Coins className="h-9 w-9" />
                  {rewards.balance}
                  <span className="text-xl">xu</span>
                </p>
              </div>
              <span className="rounded-2xl bg-white/15 p-3">
                <Sparkles className="h-7 w-7" />
              </span>
            </div>
            <div className="mt-8 rounded-2xl bg-black/10 p-4">
              <div className="flex items-center justify-between text-sm font-bold">
                <span className="inline-flex items-center gap-2">
                  <Flame className="h-4 w-4" />
                  Chuỗi điểm danh
                </span>
                <span>{rewards.checkinStreak} ngày</span>
              </div>
              <div className="mt-3 grid grid-cols-7 gap-2">
                {Array.from({ length: 7 }, (_, index) => {
                  const completed = index < rewards.checkinStreak % 7;
                  return (
                    <span
                      key={index}
                      className={`flex aspect-square items-center justify-center rounded-full text-xs font-extrabold ${
                        completed ? "bg-white text-orange-600" : "bg-white/20"
                      }`}
                      aria-label={`Ngày ${index + 1}${
                        completed ? " đã điểm danh" : ""
                      }`}
                    >
                      {index + 1}
                    </span>
                  );
                })}
              </div>
              <p className="mt-3 text-xs text-white/85">
                Mốc 7 ngày nhận 50 xu; điểm danh thường nhận 10 xu.
              </p>
            </div>
          </section>

          <section className="flex flex-col rounded-3xl border border-border bg-surface p-6 sm:p-8">
            <div className="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-300">
              <CalendarCheck className="h-6 w-6" />
            </div>
            <h2 className="mt-4 text-xl font-black text-fg">
              Điểm danh hôm nay
            </h2>
            <p className="mt-2 flex-1 text-sm leading-6 text-muted">
              Duy trì hoạt động mỗi ngày để tích lũy xu và nhận thêm thưởng theo
              chuỗi 7 ngày.
            </p>
            <Button
              type="button"
              disabled={checkingIn || rewards.checkedInToday}
              onClick={() => void onCheckin()}
              className="mt-6 w-full"
            >
              {checkingIn ? (
                <LoaderCircle className="h-4 w-4 animate-spin" />
              ) : (
                <CalendarCheck className="h-4 w-4" />
              )}
              {rewards.checkedInToday ? "Đã điểm danh hôm nay" : "Điểm danh + xu"}
            </Button>
          </section>

          <section className="rounded-2xl border border-border bg-surface p-5 lg:col-span-2">
            <h2 className="font-extrabold text-fg">Dùng xu trên chợ</h2>
            <div className="mt-4 grid gap-3 sm:grid-cols-2">
              <div className="rounded-xl bg-surface-2 p-4">
                <p className="font-bold text-fg">Làm mới tin đăng</p>
                <p className="mt-1 text-sm text-muted">
                  Dùng 50 xu để đưa tin đang hoạt động lên đầu danh sách. Mở{" "}
                  <Link
                    href="/my-listings"
                    className="font-bold text-primary hover:underline"
                  >
                    Tin đăng của tôi
                  </Link>{" "}
                  để thực hiện.
                </p>
              </div>
              <div className="rounded-xl bg-surface-2 p-4">
                <p className="font-bold text-fg">Phần thưởng tiếp theo</p>
                <p className="mt-1 text-sm text-muted">
                  Dự kiến nhận {rewards.nextCheckinReward} xu ở lần điểm danh
                  tiếp theo.
                </p>
              </div>
            </div>
          </section>
        </div>
      )}
    </UserFlowLayout>
  );
}

export default function RewardsPage() {
  return (
    <UserFlowApp>
      <RewardsContent />
    </UserFlowApp>
  );
}
