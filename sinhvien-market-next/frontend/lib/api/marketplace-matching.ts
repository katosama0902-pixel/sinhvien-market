import { apiRequest } from "./client";

export interface MatchingProduct {
  id: number;
  title: string;
  description: string;
  image: string | null;
  type: "sale" | "exchange" | "auction";
  price: number | null;
  condition: string;
  categoryId: number;
  categoryName: string;
  sellerName: string;
  sellerVerified: boolean;
  matchScore: number;
  matchReason: string;
}
export interface MatchingResult {
  modelKey: string;
  method: string;
  limit: number;
  results: MatchingProduct[];
}
export interface MatchingEvaluation {
  id: number;
  query_text: string;
  relevant_product_ids: number[];
  result_product_ids: number[];
  precision_at_5: string;
  recall_at_5: string;
  mrr_at_5: string;
  ndcg_at_5: string;
  model_key: string;
  evaluator_name: string;
  created_at: string;
}

export function searchMarketplaceMatching(query: string, filters: {
  categoryId?: number; priceMin?: number; priceMax?: number;
  condition?: string; type?: string;
} = {}) {
  const params = new URLSearchParams({ q: query });
  Object.entries(filters).forEach(([key, value]) => {
    if (value !== undefined && value !== "") params.set(key, String(value));
  });
  return apiRequest<MatchingResult>(`/marketplace/matching/search?${params.toString()}`);
}
export const indexMarketplaceProducts = () =>
  apiRequest<{ indexedCandidates: number; modelKey: string }>(
    "/admin/marketplace/matching/index", { method: "POST" },
  );
export const getMatchingEvaluations = () =>
  apiRequest<MatchingEvaluation[]>("/admin/marketplace/matching/evaluations");
export const saveMatchingEvaluation = (queryText: string, relevantProductIds: number[]) =>
  apiRequest<{
    id: number; metrics: {
      precisionAt5: number; recallAt5: number; mrrAt5: number; ndcgAt5: number;
    }; resultProductIds: number[];
  }>("/admin/marketplace/matching/evaluations", {
    method: "POST", body: { queryText, relevantProductIds },
  });
