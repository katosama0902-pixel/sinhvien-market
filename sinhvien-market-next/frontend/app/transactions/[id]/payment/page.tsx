"use client";

import { useCallback, useEffect, useState } from "react";
import Link from "next/link";
import Image from "next/image";
import { useParams } from "next/navigation";
import {
  ArrowLeft,
  CheckCircle2,
  CreditCard,
  ExternalLink,
  LoaderCircle,
  Upload,
} from "lucide-react";
import { useMainShell } from "@/components/layout/MainShell";
import {
  FlowAlert,
  UserFlowApp,
  UserFlowLayout,
} from "@/components/flows/FlowFrame";
import { Button } from "@/components/ui/Button";
import {
  getTransactionPaymentInfo,
  startTransactionPayment,
  uploadTransactionPaymentProof,
  type TransactionPaymentInfo,
} from "@/lib/api/user-flows";

function formatCurrency(amount: number): string {
  return new Intl.NumberFormat("vi-VN", {
    style: "currency",
    currency: "VND",
    maximumFractionDigits: 0,
  }).format(amount);
}

function TransactionPaymentContent() {
  const { user, loadingUser } = useMainShell();
  const params = useParams<{ id: string }>();
  const transactionId = Number(params.id);
  const validId = Number.isSafeInteger(transactionId) && transactionId > 0;
  const [payment, setPayment] = useState<TransactionPaymentInfo | null>(null);
  const [loading, setLoading] = useState(true);
  const [working, setWorking] = useState(false);
  const [error, setError] = useState("");
  const [notice, setNotice] = useState("");
  const [proof, setProof] = useState<File | null>(null);

  const refresh = useCallback(async () => {
    if (!user || !validId) return;
    setLoading(true);
    try {
      setPayment(await getTransactionPaymentInfo(user, transactionId));
      setError("");
    } catch (cause) {
      setError(
        cause instanceof Error
          ? cause.message
          : "Không tải được thông tin thanh toán.",
      );
    } finally {
      setLoading(false);
    }
  }, [transactionId, user, validId]);

  useEffect(() => {
    if (loadingUser || !validId || !user) return;
    void Promise.resolve().then(refresh);
  }, [loadingUser, refresh, user, validId]);

  async function submitProof(event: React.FormEvent<HTMLFormElement>) {
    event.preventDefault();
    if (!user || !proof) {
      setError("Vui lòng chọn ảnh biên lai chuyển khoản.");
      return;
    }
    setWorking(true);
    setError("");
    setNotice("");
    try {
      await uploadTransactionPaymentProof(user, transactionId, proof);
      setProof(null);
      setNotice("Đã gửi biên lai. Đang chờ người bán xác nhận.");
      await refresh();
    } catch (cause) {
      setError(
        cause instanceof Error ? cause.message : "Không gửi được biên lai.",
      );
    } finally {
      setWorking(false);
    }
  }

  async function continueOnlinePayment() {
    if (!user) return;
    setWorking(true);
    setError("");
    try {
      const redirectUrl = await startTransactionPayment(user, transactionId);
      window.location.assign(redirectUrl);
    } catch (cause) {
      setError(
        cause instanceof Error
          ? cause.message
          : "Không thể khởi tạo thanh toán trực tuyến.",
      );
      setWorking(false);
    }
  }

  const canPay =
    payment?.paymentStatus === "pending" && payment.orderStatus === "pending";
  const visibleError = validId ? error : "Mã giao dịch không hợp lệ.";

  return (
    <UserFlowLayout
      title="Thanh toán giao dịch"
      description="Thông tin thanh toán được xử lý an toàn qua backend SinhVienMarket."
    >
      <FlowAlert error={visibleError} success={notice} />
      <div className="mb-5">
        <Link
          href="/transactions"
          className="inline-flex items-center gap-2 text-sm font-bold text-muted hover:text-primary"
        >
          <ArrowLeft className="h-4 w-4" />
          Quay lại giao dịch
        </Link>
      </div>

      {!validId ? (
        <div className="rounded-2xl border border-border bg-surface p-8 text-center text-sm text-muted">
          Mã giao dịch không hợp lệ.
        </div>
      ) : loading ? (
        <div className="rounded-2xl border border-border bg-surface py-16 text-center text-muted">
          <LoaderCircle className="mx-auto mb-2 h-6 w-6 animate-spin" />
          Đang tải thông tin thanh toán...
        </div>
      ) : payment ? (
        <section className="mx-auto max-w-3xl overflow-hidden rounded-2xl border border-border bg-surface">
          <header className="border-b border-border bg-surface-2 p-6 sm:p-8">
            <p className="text-xs font-bold uppercase tracking-wider text-muted">
              Giao dịch #{payment.id}
            </p>
            <div className="mt-2 flex flex-wrap items-end justify-between gap-3">
              <h2 className="text-2xl font-black text-fg">
                {formatCurrency(payment.amount)}
              </h2>
              <span className="rounded-full bg-primary/10 px-3 py-1 text-xs font-bold text-primary">
                {payment.paymentMethod === "banking"
                  ? "Chuyển khoản ngân hàng"
                  : payment.paymentMethod === "zalopay"
                    ? "ZaloPay"
                    : payment.paymentMethod === "momo"
                      ? "MoMo"
                      : "Thanh toán khi nhận hàng"}
              </span>
            </div>
          </header>

          <div className="space-y-6 p-6 sm:p-8">
            {payment.paymentStatus === "paid" ? (
              <div className="rounded-xl border border-emerald-300 bg-emerald-50 p-4 text-sm text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/30 dark:text-emerald-200">
                <CheckCircle2 className="mr-2 inline h-5 w-5" />
                Thanh toán đã được xác nhận. Theo dõi tiến độ tại trang giao dịch.
              </div>
            ) : payment.paymentStatus === "refunded" ? (
              <div className="rounded-xl border border-amber-300 bg-amber-50 p-4 text-sm text-amber-800 dark:border-amber-900 dark:bg-amber-950/30 dark:text-amber-200">
                Giao dịch đã được hoàn tiền.
              </div>
            ) : payment.paymentMethod === "banking" ? (
              payment.hasPaymentProof ? (
                <div className="rounded-xl border border-emerald-300 bg-emerald-50 p-4 text-sm text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/30 dark:text-emerald-200">
                  <CheckCircle2 className="mr-2 inline h-5 w-5" />
                  Đã nhận biên lai. Đang chờ người bán xác nhận đã nhận tiền.
                  <a
                    href={`/transactions/proof?id=${payment.id}`}
                    target="_blank"
                    rel="noreferrer"
                    className="ml-2 inline-flex items-center gap-1 font-bold underline"
                  >
                    Xem biên lai <ExternalLink className="h-3.5 w-3.5" />
                  </a>
                </div>
              ) : !payment.bankAccount ? (
                <div className="rounded-xl border border-amber-300 bg-amber-50 p-4 text-sm text-amber-800 dark:border-amber-900 dark:bg-amber-950/30 dark:text-amber-200">
                  Người bán chưa thiết lập tài khoản nhận tiền. Vui lòng liên hệ
                  người bán hoặc quay lại giao dịch để được hỗ trợ.
                </div>
              ) : (
                <>
                  <div className="grid gap-6 sm:grid-cols-[minmax(0,1fr)_220px]">
                    <div>
                      <h3 className="mb-4 font-extrabold text-fg">
                        Thông tin chuyển khoản
                      </h3>
                      <dl className="space-y-3 text-sm">
                        <InfoRow label="Người nhận" value={payment.bankAccount.accountName} />
                        <InfoRow label="Ngân hàng" value={payment.bankAccount.bankName} />
                        <InfoRow label="Số tài khoản" value={payment.bankAccount.accountNo} />
                        <InfoRow label="Nội dung chuyển khoản" value={`DH ${payment.id}`} />
                        <InfoRow label="Số tiền" value={formatCurrency(payment.amount)} />
                      </dl>
                    </div>
                    {payment.bankAccount.qrUrl && (
                      <div className="rounded-xl border border-border bg-white p-3 text-center">
                        <Image
                          src={payment.bankAccount.qrUrl}
                          alt="Mã QR chuyển khoản VietQR"
                          width={220}
                          height={220}
                          unoptimized
                          className="mx-auto aspect-square w-full max-w-[220px] object-contain"
                        />
                        <p className="mt-2 text-xs text-slate-600">
                          Quét mã bằng ứng dụng ngân hàng
                        </p>
                      </div>
                    )}
                  </div>

                  {canPay && (
                    <form
                      onSubmit={submitProof}
                      className="rounded-xl border border-border bg-surface-2 p-4 sm:p-5"
                    >
                      <label className="block text-sm font-bold text-fg">
                        Tải ảnh biên lai chuyển khoản
                        <input
                          type="file"
                          accept="image/jpeg,image/png,image/webp,image/gif"
                          required
                          onChange={(event) =>
                            setProof(event.target.files?.[0] ?? null)
                          }
                          className="mt-2 block w-full cursor-pointer rounded-lg border border-border bg-surface p-2 text-sm text-muted file:mr-3 file:rounded-md file:border-0 file:bg-primary/10 file:px-3 file:py-2 file:font-bold file:text-primary"
                        />
                      </label>
                      <p className="mt-2 text-xs text-muted">
                        Chấp nhận JPG, PNG, WEBP hoặc GIF, tối đa 3 MB.
                      </p>
                      <Button
                        type="submit"
                        disabled={!proof || working}
                        className="mt-4"
                      >
                        {working ? (
                          <LoaderCircle className="h-4 w-4 animate-spin" />
                        ) : (
                          <Upload className="h-4 w-4" />
                        )}
                        Gửi biên lai
                      </Button>
                    </form>
                  )}
                </>
              )
            ) : payment.paymentMethod === "zalopay" ||
              payment.paymentMethod === "momo" ? (
              canPay ? (
                <div className="space-y-4">
                  <p className="text-sm leading-6 text-muted">
                    Tiếp tục đến cổng thanh toán để hoàn tất giao dịch. Trạng
                    thái sẽ được cập nhật sau khi backend nhận callback/IPN.
                  </p>
                  <Button
                    type="button"
                    disabled={working}
                    onClick={() => void continueOnlinePayment()}
                  >
                    {working ? (
                      <LoaderCircle className="h-4 w-4 animate-spin" />
                    ) : (
                      <CreditCard className="h-4 w-4" />
                    )}
                    Tiếp tục thanh toán {payment.paymentMethod === "zalopay" ? "ZaloPay" : "MoMo"}
                  </Button>
                </div>
              ) : (
                <p className="text-sm text-muted">
                  Giao dịch không còn ở trạng thái chờ thanh toán.
                </p>
              )
            ) : (
              <p className="text-sm text-muted">
                Phương thức này không cần thanh toán trực tuyến. Xem tiến độ tại
                trang giao dịch.
              </p>
            )}
          </div>
        </section>
      ) : (
        !error && (
          <div className="rounded-2xl border border-border bg-surface p-8 text-center text-sm text-muted">
            Không tìm thấy thông tin thanh toán.
          </div>
        )
      )}
    </UserFlowLayout>
  );
}

function InfoRow({ label, value }: { label: string; value: string }) {
  return (
    <div className="flex flex-wrap justify-between gap-2 border-b border-border pb-2 last:border-0">
      <dt className="text-muted">{label}</dt>
      <dd className="text-right font-bold text-fg">{value}</dd>
    </div>
  );
}

export default function TransactionPaymentPage() {
  return (
    <UserFlowApp>
      <TransactionPaymentContent />
    </UserFlowApp>
  );
}
