<?php

namespace App\Services;

use App\Models\MarketplaceMatching;

class MarketplaceMatchingService
{
    public function __construct(
        private readonly MarketplaceMatching $model = new MarketplaceMatching(),
        private readonly LocalMarketplaceEmbeddingService $embeddings = new LocalMarketplaceEmbeddingService()
    ) {
    }

    public function search(string $query, array $filters = [], int $limit = 20): array
    {
        $queryVector = $this->embeddings->embed($query);
        $rows = $this->model->candidates($filters);
        $ranked = [];
        foreach ($rows as $product) {
            $vector = $this->embedProduct($product);
            $product['matchScore'] = round($this->embeddings->cosine($queryVector, $vector), 6);
            $product['matchReason'] = 'Tương đồng từ khóa và cụm từ trong tiêu đề/mô tả (baseline cục bộ).';
            $ranked[] = $product;
        }
        usort($ranked, static fn(array $a, array $b): int =>
            ($b['matchScore'] <=> $a['matchScore']) ?: ((int)$b['id'] <=> (int)$a['id'])
        );
        return [
            'modelKey' => LocalMarketplaceEmbeddingService::MODEL_KEY,
            'method' => 'feature_hash_cosine',
            'limit' => max(1, min($limit, 50)),
            'results' => array_slice($ranked, 0, max(1, min($limit, 50))),
        ];
    }

    public function indexProduct(array $product): void
    {
        $this->embedProduct($product);
    }

    private function embedProduct(array $product): array
    {
        $text = $product['title'] . ' ' . $product['description'] . ' ' . $product['category_name'];
        $hash = hash('sha256', $text);
        $vector = $this->model->savedEmbedding(
            (int)$product['id'],
            LocalMarketplaceEmbeddingService::MODEL_KEY,
            $hash
        );
        if ($vector === null) {
            $vector = $this->embeddings->embed($text);
            $this->model->saveEmbedding(
                (int)$product['id'],
                LocalMarketplaceEmbeddingService::MODEL_KEY,
                $vector,
                $hash
            );
        }
        return $vector;
    }
}
