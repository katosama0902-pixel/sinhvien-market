import { NextResponse } from "next/server";
import fs from "node:fs";
import path from "node:path";
import rawOffers from "@/lib/data/offers.json";
import { computeKeywordBm25Scores } from "@/lib/matching/engine";
import type { MarketplaceOffer } from "@/lib/types/matching";

interface VectorRecord {
  listing_id: string;
  modality: string;
  vector: number[];
}

interface VectorCacheFile {
  model_id: string;
  model_version: string;
  records: Record<string, VectorRecord>;
}

function clamp01(val: number): number {
  return Math.max(0, Math.min(1, val));
}

function calGeminiSame(c: number): number {
  return clamp01((c - 0.45) / (0.83 - 0.45));
}

function calGeminiCross(c: number): number {
  return clamp01((c - 0.3) / (0.44 - 0.3));
}

function calJinaSame(c: number): number {
  return clamp01((c - 0.38) / (0.78 - 0.38));
}

function calJinaCross(c: number): number {
  return clamp01((c - 0.21) / (0.33 - 0.21));
}

function parseDotEnvFile(filePath: string): Record<string, string> {
  const out: Record<string, string> = {};
  if (!fs.existsSync(filePath)) return out;
  const content = fs.readFileSync(filePath, "utf-8");
  for (const rawLine of content.split(/\r?\n/)) {
    const line = rawLine.trim();
    if (!line || line.startsWith("#")) continue;
    const eqIdx = line.indexOf("=");
    if (eqIdx <= 0) continue;
    const key = line.slice(0, eqIdx).trim();
    const val = line.slice(eqIdx + 1).trim();
    out[key] = val;
  }
  return out;
}

function getAiLabDir(): string {
  return path.resolve(process.cwd(), "..", "ai-lab");
}

function getEnvValue(name: string): string {
  if (process.env[name]) return process.env[name] ?? "";
  const labEnv = parseDotEnvFile(path.join(getAiLabDir(), ".env"));
  return labEnv[name] ?? "";
}

function cosineSim(a: number[], b: number[]): number {
  if (a.length !== b.length || a.length === 0) return 0;
  let dot = 0;
  let na = 0;
  let nb = 0;
  for (let i = 0; i < a.length; i += 1) {
    dot += a[i] * b[i];
    na += a[i] * a[i];
    nb += b[i] * b[i];
  }
  if (na <= 1e-12 || nb <= 1e-12) return 0;
  return dot / (Math.sqrt(na) * Math.sqrt(nb));
}

function l2Normalize(vec: number[]): number[] {
  const norm = Math.sqrt(vec.reduce((acc, v) => acc + v * v, 0));
  if (norm <= 1e-12) return vec;
  return vec.map((v) => v / norm);
}

function findCacheFile(prefix: string): VectorCacheFile | null {
  const cacheDir = path.join(getAiLabDir(), "cache");
  if (!fs.existsSync(cacheDir)) return null;
  const files = fs
    .readdirSync(cacheDir)
    .filter((f) => f.startsWith(prefix) && f.endsWith(".json"));
  if (files.length === 0) return null;
  return JSON.parse(
    fs.readFileSync(path.join(cacheDir, files[0]), "utf-8"),
  ) as VectorCacheFile;
}

async function callGeminiEmbed(
  parts: Array<Record<string, unknown>>,
  taskType: string | null,
): Promise<number[] | null> {
  const rawKeys =
    getEnvValue("GEMINI_API_KEYS") || getEnvValue("GEMINI_API_KEY");
  const keys = rawKeys
    .split(",")
    .map((k) => k.trim())
    .filter(Boolean);
  if (keys.length === 0) return null;

  const model = getEnvValue("GEMINI_MODEL") || "gemini-embedding-2";
  const outputDim = Number(getEnvValue("GEMINI_OUTPUT_DIM") || "768");

  for (const apiKey of keys) {
    const url = `https://generativelanguage.googleapis.com/v1beta/models/${model}:embedContent?key=${encodeURIComponent(apiKey)}`;
    const payload: Record<string, unknown> = {
      model: `models/${model}`,
      content: { parts },
      outputDimensionality: outputDim,
    };
    if (taskType) payload.taskType = taskType;

    const resp = await fetch(url, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload),
    });
    if (resp.status === 429) continue;
    if (!resp.ok) return null;
    const json = (await resp.json()) as { embedding?: { values?: number[] } };
    if (json.embedding?.values) return l2Normalize(json.embedding.values);
  }
  return null;
}

async function embedJinaQuery(
  text: string,
  imageDataUrl: string | null,
  multimodal: boolean,
): Promise<{ textVec: number[]; imgVec: number[] | null } | null> {
  const apiKey = getEnvValue("JINA_API_KEY");
  if (!apiKey) return null;
  const outputDim = Number(getEnvValue("JINA_OUTPUT_DIM") || "1024");

  const inputs: Array<Record<string, string>> = [{ text }];
  if (multimodal && imageDataUrl && imageDataUrl.startsWith("data:")) {
    inputs.push({ image: imageDataUrl });
  }

  const resp = await fetch("https://api.jina.ai/v1/embeddings", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      Authorization: `Bearer ${apiKey}`,
    },
    body: JSON.stringify({
      model: "jina-clip-v2",
      dimensions: outputDim,
      normalized: true,
      input: inputs,
    }),
  });
  if (!resp.ok) return null;
  const json = (await resp.json()) as {
    data: Array<{ index: number; embedding: number[] }>;
  };
  const sorted = [...json.data].sort((a, b) => a.index - b.index);
  const textVec = sorted[0]?.embedding;
  if (!textVec) return null;
  return { textVec, imgVec: sorted[1]?.embedding ?? null };
}

export async function POST(req: Request) {
  try {
    const body = (await req.json()) as {
      text?: string;
      imageDataUrl?: string | null;
      modelId?: string;
    };
    const text = (body.text ?? "").trim();
    const imageDataUrl = body.imageDataUrl ?? null;
    const modelId = body.modelId ?? "jina-clip-v2-multimodal";

    if (!text) {
      return NextResponse.json(
        { error: "Mô tả nhu cầu không được để trống." },
        { status: 400 },
      );
    }

    const t0 = performance.now();

    if (modelId.startsWith("gemini-embedding-2")) {
      const multimodal = modelId.endsWith("multimodal");
      const cache = findCacheFile("vectors_gemini-embedding-2__");
      const qTextVec = await callGeminiEmbed([{ text }], "RETRIEVAL_QUERY");
      if (!cache || !qTextVec) {
        return NextResponse.json({ live: false, scores: null });
      }

      let qImgVec: number[] | null = null;
      if (multimodal && imageDataUrl && imageDataUrl.startsWith("data:")) {
        const commaIdx = imageDataUrl.indexOf(",");
        const mimeType =
          imageDataUrl.slice(5, commaIdx).split(";")[0] || "image/jpeg";
        const base64Data = imageDataUrl.slice(commaIdx + 1);
        qImgVec = await callGeminiEmbed(
          [{ inline_data: { mime_type: mimeType, data: base64Data } }],
          null,
        );
      }

      const dTextVecs: Record<string, number[]> = {};
      const dImgVecs: Record<string, number[]> = {};
      for (const rec of Object.values(cache.records)) {
        if (!rec.listing_id.startsWith("O")) continue;
        if (rec.modality === "text_retrieval_document") {
          dTextVecs[rec.listing_id] = rec.vector;
        } else if (multimodal && rec.modality === "image") {
          dImgVecs[rec.listing_id] = rec.vector;
        }
      }

      const lexicalScores = computeKeywordBm25Scores(
        text,
        rawOffers as MarketplaceOffer[],
      );
      const tiRaws: Record<string, number> = {};
      for (const [oid, dImg] of Object.entries(dImgVecs)) {
        tiRaws[oid] = cosineSim(qTextVec, dImg);
      }
      const tiVals = Object.values(tiRaws);
      const meanTi =
        tiVals.length > 0
          ? tiVals.reduce((a, b) => a + b, 0) / tiVals.length
          : 0;

      const itRaws: Record<string, number> = {};
      if (qImgVec) {
        for (const [oid, dText] of Object.entries(dTextVecs)) {
          itRaws[oid] = cosineSim(qImgVec, dText);
        }
      }
      const itVals = Object.values(itRaws);
      const meanIt =
        itVals.length > 0
          ? itVals.reduce((a, b) => a + b, 0) / itVals.length
          : 0;

      const scores: Record<string, number> = {};
      for (const [oid, dText] of Object.entries(dTextVecs)) {
        const dImg = dImgVecs[oid] ?? null;
        const sTt = calGeminiSame(cosineSim(qTextVec, dText));
        const kw = lexicalScores[oid] ?? 0;
        const textChan = kw > 0 ? clamp01(0.72 * sTt + 0.28 * kw) : sTt * 0.56;
        const cands = [textChan];

        if (dImg) {
          const marginTi = (tiRaws[oid] ?? 0) - meanTi;
          const sTiAbs = calGeminiCross(tiRaws[oid] ?? 0);
          const sTiMargin = clamp01((marginTi - 0.025) / (0.1 - 0.025));
          cands.push(Math.min(sTiAbs, sTiMargin) * 0.85 + 0.15 * sTt);
        }
        if (qImgVec) {
          const marginIt = (itRaws[oid] ?? 0) - meanIt;
          const sItAbs = clamp01(((itRaws[oid] ?? 0) - 0.28) / (0.4 - 0.28));
          const sItMargin = clamp01((marginIt - 0.03) / (0.12 - 0.03));
          cands.push(Math.min(sItAbs, sItMargin) * 0.85 + 0.15 * sTt);
        }
        if (qImgVec && dImg) {
          cands.push(clamp01((cosineSim(qImgVec, dImg) - 0.52) / (0.86 - 0.52)));
        }
        scores[oid] = Number(Math.max(...cands).toFixed(4));
      }

      return NextResponse.json({
        live: true,
        latencyMs: Number((performance.now() - t0).toFixed(1)),
        scores,
      });
    }

    if (modelId.startsWith("jina-clip-v2")) {
      const multimodal = modelId.endsWith("multimodal");
      const cache = findCacheFile("vectors_jina-clip-v2__");
      const qPair = await embedJinaQuery(text, imageDataUrl, multimodal);
      if (!cache || !qPair) {
        return NextResponse.json({ live: false, scores: null });
      }

      const dTextVecs: Record<string, number[]> = {};
      const dImgVecs: Record<string, number[]> = {};
      for (const rec of Object.values(cache.records)) {
        if (!rec.listing_id.startsWith("O")) continue;
        if (rec.modality === "text_clip") {
          dTextVecs[rec.listing_id] = rec.vector;
        } else if (multimodal && rec.modality === "image") {
          dImgVecs[rec.listing_id] = rec.vector;
        }
      }

      const lexicalScores = computeKeywordBm25Scores(
        text,
        rawOffers as MarketplaceOffer[],
      );
      const tiRaws: Record<string, number> = {};
      for (const [oid, dImg] of Object.entries(dImgVecs)) {
        tiRaws[oid] = cosineSim(qPair.textVec, dImg);
      }
      const tiVals = Object.values(tiRaws);
      const meanTi =
        tiVals.length > 0
          ? tiVals.reduce((a, b) => a + b, 0) / tiVals.length
          : 0;

      const itRaws: Record<string, number> = {};
      if (qPair.imgVec) {
        for (const [oid, dText] of Object.entries(dTextVecs)) {
          itRaws[oid] = cosineSim(qPair.imgVec, dText);
        }
      }
      const itVals = Object.values(itRaws);
      const meanIt =
        itVals.length > 0
          ? itVals.reduce((a, b) => a + b, 0) / itVals.length
          : 0;

      const scores: Record<string, number> = {};
      for (const [oid, dText] of Object.entries(dTextVecs)) {
        const dImg = dImgVecs[oid] ?? null;
        const sTt = calJinaSame(cosineSim(qPair.textVec, dText));
        const kw = lexicalScores[oid] ?? 0;
        const textChan = kw > 0 ? clamp01(0.72 * sTt + 0.28 * kw) : sTt * 0.56;
        const cands = [textChan];

        if (dImg) {
          const marginTi = (tiRaws[oid] ?? 0) - meanTi;
          const sTiAbs = calJinaCross(tiRaws[oid] ?? 0);
          const sTiMargin = clamp01((marginTi - 0.025) / (0.1 - 0.025));
          cands.push(Math.min(sTiAbs, sTiMargin) * 0.85 + 0.15 * sTt);
        }
        if (qPair.imgVec) {
          const marginIt = (itRaws[oid] ?? 0) - meanIt;
          const sItAbs = calJinaCross(itRaws[oid] ?? 0);
          const sItMargin = clamp01((marginIt - 0.035) / (0.12 - 0.035));
          cands.push(Math.min(sItAbs, sItMargin) * 0.85 + 0.15 * sTt);
        }
        if (qPair.imgVec && dImg) {
          cands.push(
            clamp01((cosineSim(qPair.imgVec, dImg) - 0.45) / (0.85 - 0.45)),
          );
        }
        scores[oid] = Number(Math.max(...cands).toFixed(4));
      }

      return NextResponse.json({
        live: true,
        latencyMs: Number((performance.now() - t0).toFixed(1)),
        scores,
      });
    }

    return NextResponse.json({ live: false, scores: null });
  } catch {
    return NextResponse.json({ live: false, scores: null });
  }
}
