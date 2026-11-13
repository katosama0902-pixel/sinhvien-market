"use client";

import { useEffect, useState, type FormEvent } from "react";
import { MainShell, useMainShell } from "@/components/layout/MainShell";
import {
  getRentalApplications, getRentalContracts, getRentalSpaces,
  submitRentalApplication, type RentalApplication, type RentalContract,
  type RentalSpace,
} from "@/lib/api/residence-operations";

function RentalsContent() {
  const { user, loadingUser } = useMainShell();
  const [spaces, setSpaces] = useState<RentalSpace[]>([]);
  const [applications, setApplications] = useState<RentalApplication[]>([]);
  const [contracts, setContracts] = useState<RentalContract[]>([]);
  const [form, setForm] = useState({ spaceId: "", startDate: "", endDate: "", businessName: "", note: "" });
  const [error, setError] = useState("");
  const [notice, setNotice] = useState("");
  const [busy, setBusy] = useState(false);
  async function load() {
    const [nextSpaces, nextApps, nextContracts] = await Promise.all([
      getRentalSpaces(), getRentalApplications(), getRentalContracts(),
    ]);
    setSpaces(nextSpaces); setApplications(nextApps); setContracts(nextContracts);
  }
  useEffect(() => {
    if (loadingUser || !user) return;
    let active = true;
    async function initialize() {
      try {
        const [nextSpaces, nextApps, nextContracts] = await Promise.all([
          getRentalSpaces(), getRentalApplications(), getRentalContracts(),
        ]);
        if (!active) return;
        setSpaces(nextSpaces); setApplications(nextApps); setContracts(nextContracts);
      } catch (cause) {
        if (active) setError(cause instanceof Error ? cause.message : "Không tải được mặt bằng.");
      }
    }
    void initialize();
    return () => { active = false; };
  }, [loadingUser, user]);
  async function submit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault(); setBusy(true); setError(""); setNotice("");
    try {
      await submitRentalApplication({ ...form, spaceId: Number(form.spaceId) });
      setForm({ spaceId: "", startDate: "", endDate: "", businessName: "", note: "" });
      setNotice("Đã gửi đề nghị thuê để xét duyệt."); await load();
    } catch (cause) { setError(cause instanceof Error ? cause.message : "Không gửi được đề nghị thuê."); }
    finally { setBusy(false); }
  }
  return <main className="mx-auto max-w-6xl space-y-6 px-4 py-8">
    <header><p className="text-sm font-bold uppercase tracking-widest text-primary">KTX SinhVienMarket</p><h1 className="mt-2 text-3xl font-black text-fg">Mặt bằng cho thuê</h1><p className="mt-2 text-sm text-muted">Xem mặt bằng, gửi đề nghị thuê và theo dõi hợp đồng.</p></header>
    {error && <p role="alert" className="rounded-xl bg-red-50 p-4 text-red-800">{error}</p>}
    {notice && <p role="status" className="rounded-xl bg-emerald-50 p-4 text-emerald-800">{notice}</p>}
    {!user && !loadingUser && <p className="rounded-xl border border-border p-5">Đăng nhập với tài khoản đối tác/người thuê để tiếp tục.</p>}
    <section className="grid gap-4 md:grid-cols-2">{spaces.map((space) => <article key={space.id} className="rounded-2xl border border-border bg-surface p-5"><p className="text-xs font-bold uppercase text-primary">{space.spaceCode} · {space.status}</p><h2 className="mt-2 text-xl font-bold">{space.name}</h2><p className="mt-1 text-sm text-muted">{space.location} · {space.areaSqm} m²</p><p className="mt-3 font-semibold">{Number(space.monthlyPrice).toLocaleString("vi-VN")} / tháng</p>{space.description && <p className="mt-2 text-sm">{space.description}</p>}</article>)}{spaces.length === 0 && <p className="text-sm text-muted">Hiện chưa có mặt bằng được đăng.</p>}</section>
    {user && <form onSubmit={submit} className="grid gap-4 rounded-2xl border border-border bg-surface p-5 sm:grid-cols-2">
      <h2 className="text-xl font-bold sm:col-span-2">Gửi đề nghị thuê</h2>
      <label className="text-sm font-semibold sm:col-span-2">Mặt bằng<select required value={form.spaceId} onChange={(e) => setForm({ ...form, spaceId: e.target.value })} className="mt-1 w-full rounded-lg border border-border bg-bg p-3"><option value="">Chọn mặt bằng còn trống</option>{spaces.filter((space) => space.status === "available").map((space) => <option key={space.id} value={space.id}>{space.spaceCode} · {space.name} · {space.monthlyPrice}/tháng</option>)}</select></label>
      <label className="text-sm font-semibold">Ngày bắt đầu<input required type="date" value={form.startDate} onChange={(e) => setForm({ ...form, startDate: e.target.value })} className="mt-1 w-full rounded-lg border border-border bg-bg p-3" /></label>
      <label className="text-sm font-semibold">Ngày kết thúc<input required type="date" value={form.endDate} onChange={(e) => setForm({ ...form, endDate: e.target.value })} className="mt-1 w-full rounded-lg border border-border bg-bg p-3" /></label>
      <label className="text-sm font-semibold">Tên đơn vị kinh doanh<input required maxLength={150} value={form.businessName} onChange={(e) => setForm({ ...form, businessName: e.target.value })} className="mt-1 w-full rounded-lg border border-border bg-bg p-3" /></label>
      <label className="text-sm font-semibold">Ghi chú<input maxLength={1000} value={form.note} onChange={(e) => setForm({ ...form, note: e.target.value })} className="mt-1 w-full rounded-lg border border-border bg-bg p-3" /></label>
      <button disabled={busy} className="rounded-xl bg-primary px-4 py-3 font-bold text-white disabled:opacity-50">Gửi đề nghị thuê</button>
    </form>}
    {user && <section className="grid gap-6 md:grid-cols-2"><div className="rounded-2xl border border-border bg-surface p-5"><h2 className="text-xl font-bold">Đề nghị của tôi</h2>{applications.map((app) => <p key={app.id} className="border-b border-border py-3 text-sm">{app.applicationNumber} · {app.spaceName} · {app.status} · {app.startDate} – {app.endDate}{app.contractNumber && ` · ${app.contractNumber}`}</p>)}</div><div className="rounded-2xl border border-border bg-surface p-5"><h2 className="text-xl font-bold">Hợp đồng</h2>{contracts.map((contract) => <p key={contract.id} className="border-b border-border py-3 text-sm">{contract.contractNumber} · {contract.spaceName} · {contract.status} · {contract.startDate} – {contract.endDate} · {Number(contract.monthlyPrice).toLocaleString("vi-VN")}/tháng</p>)}</div></section>}
  </main>;
}
export default function RentalsPage() { return <MainShell><RentalsContent /></MainShell>; }
