<?php

namespace App\Controllers;

use App\Models\Auction;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Giveaway;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Core\ApiController;

/**
 * Public, read-only JSON endpoints for the Next.js marketplace browsing UI.
 *
 * No seller contact details or giveaway participant identities are exposed.
 */
class MarketplaceApiController extends ApiController
{
    private Product $products;
    private Category $categories;
    private Banner $banners;
    private Giveaway $giveaways;

    public function __construct()
    {
        $this->products = new Product();
        $this->categories = new Category();
        $this->banners = new Banner();
        $this->giveaways = new Giveaway();
    }

    /**
     * GET /api/marketplace/home
     *
     * data: {
     *   banners: MarketplaceBanner[],
     *   categories: MarketplaceCategory[],
     *   featuredProducts: MarketplaceProduct[],
     *   auctionProducts: MarketplaceProduct[],
     *   giveaway: MarketplaceGiveaway|null,
     *   stats: {activeProducts:number,totalUsers:number,txToday:number}
     * }
     */
    public function home(): void
    {
        $featured = $this->products->getActive(6);
        $auctions = $this->products->getActiveAuctions(3);
        $activeGiveaways = $this->giveaways->getActive();
        $giveaway = $activeGiveaways[0] ?? null;

        $userModel = new User();
        $transactionModel = new Transaction();

        $this->success([
            'banners' => array_map(
                fn (array $banner): array => $this->bannerDto($banner),
                $this->banners->getActiveBanners()
            ),
            'categories' => array_map(
                fn (array $category): array => $this->categoryDto($category),
                $this->categories->all()
            ),
            'featuredProducts' => array_map(
                fn (array $product): array => $this->productDto($product),
                $featured
            ),
            'auctionProducts' => array_map(
                fn (array $product): array => $this->productDto($product),
                $auctions
            ),
            'giveaway' => $giveaway ? $this->giveawayDto($giveaway) : null,
            'stats' => [
                'activeProducts' => $this->products->countActive(),
                'totalUsers' => $userModel->countAll(),
                'txToday' => $transactionModel->countToday(),
            ],
        ]);
    }

    /**
     * GET /api/marketplace/categories
     * data: MarketplaceCategory[]; each category has id, name, slug, icon, createdAt.
     */
    public function categories(): void
    {
        $this->success(array_map(
            fn (array $category): array => $this->categoryDto($category),
            $this->categories->all()
        ));
    }

    /**
     * GET /api/marketplace/leaderboard
     * Public seller rankings with optional current-user rank from the session.
     */
    public function leaderboard(): void
    {
        $users = new User();
        $topSellers = array_map(
            fn(array $seller): array => $this->leaderboardDto($seller),
            $users->getLeaderboard(10)
        );
        $userId = (int)($_SESSION['user']['id'] ?? 0);
        $myRank = $userId > 0 ? $users->getMyRank($userId) : 0;
        $myStats = $userId > 0 ? $users->getLeaderboardUser($userId) : null;

        $this->success([
            'topSellers' => $topSellers,
            'myRank' => $myRank,
            'myStats' => $myStats ? $this->leaderboardDto($myStats) : null,
        ]);
    }

    /**
     * GET /api/marketplace/products
     *
     * Query filters: q, category, type, condition, priceMin, priceMax.
     * Optional page/limit default to 1/12; limit is capped at 50.
     * data is an array of public MarketplaceProduct DTOs.
     */
    public function products(): void
    {
        $keyword = trim($this->queryParameterString('q'));
        if (mb_strlen($keyword, 'UTF-8') > 120) {
            $this->validationError('Từ khóa tìm kiếm không được vượt quá 120 ký tự.');
        }

        $categoryId = $this->queryParameterInt('category', 0);
        $priceMin = $this->queryParameterInt('priceMin', 0);
        $priceMax = $this->queryParameterInt('priceMax', 0);
        $page = $this->queryParameterInt('page', 1);
        $limit = $this->queryParameterInt('limit', 12);
        $type = $this->queryParameterString('type');
        $condition = $this->queryParameterString('condition');

        if ($categoryId < 0 || $priceMin < 0 || $priceMax < 0 || $page < 1 || $limit < 1) {
            $this->validationError('Tham số lọc sản phẩm không hợp lệ.');
        }
        if ($priceMin > 0 && $priceMax > 0 && $priceMin > $priceMax) {
            $this->validationError('Giá thấp nhất không được lớn hơn giá cao nhất.');
        }
        if ($type !== '' && !in_array($type, ['sale', 'exchange', 'auction'], true)) {
            $this->validationError('Loại sản phẩm không hợp lệ.');
        }
        if ($condition !== '' && !in_array($condition, ['new', 'like_new', 'used', 'worn'], true)) {
            $this->validationError('Tình trạng sản phẩm không hợp lệ.');
        }

        $limit = min($limit, 50);
        $offset = ($page - 1) * $limit;
        if ($keyword !== '') {
            $rows = $this->products->search(
                $keyword,
                $categoryId,
                $limit,
                $offset,
                $condition,
                $priceMin,
                $priceMax,
                $type
            );
        } else {
            $rows = $this->products->getActive(
                $limit,
                $offset,
                $categoryId,
                $condition,
                $priceMin,
                $priceMax,
                $type
            );
        }

        $this->success(array_map(
            fn (array $product): array => $this->productDto($product),
            $rows
        ));
    }

    /**
     * GET /api/marketplace/product?id=<id>
     *
     * data is one MarketplaceProductDetail:
     * AdminProduct-compatible public fields (without sellerEmail), plus
     * description, imageUrl, sellerVerified, createdAt, and nullable auction.
     * Only active products are public through this endpoint.
     */
    public function product(): void
    {
        $id = $this->queryParameterInt('id', 0);
        if ($id < 1) {
            $this->validationError('Mã sản phẩm không hợp lệ.');
        }

        $product = $this->products->findWithAuction($id);
        if (!$product || ($product['status'] ?? '') !== 'active') {
            $this->notFound('Không tìm thấy sản phẩm.');
        }

        $this->success($this->productDto($product, true));
    }

    /** @return array<string, mixed> */
    private function productDto(array $row, bool $includeDescription = false): array
    {
        $id = (int)$row['id'];
        $hasImage = !empty($row['image']);
        $dto = [
            'id' => $id,
            'title' => (string)($row['title'] ?? ''),
            'categoryId' => (int)($row['category_id'] ?? 0),
            'categoryName' => (string)($row['category_name'] ?? ''),
            'sellerId' => (int)($row['user_id'] ?? 0),
            'sellerName' => (string)($row['seller_name'] ?? 'Người dùng ẩn'),
            'sellerVerified' => (bool)($row['seller_verified'] ?? false),
            'type' => (string)($row['type'] ?? ''),
            'price' => isset($row['price']) ? (int)$row['price'] : null,
            'condition' => (string)($row['condition'] ?? ''),
            'status' => (string)($row['status'] ?? ''),
            'imageUrl' => $hasImage ? $this->publicUrl('/products/image?id=' . $id) : null,
            'createdAt' => $row['created_at'] ?? null,
            'currentPrice' => null,
            'isAtFloor' => null,
            'auction' => null,
        ];

        if ($includeDescription) {
            $dto['description'] = (string)($row['description'] ?? '');
        }

        if (($row['type'] ?? '') === 'auction' && !empty($row['auction_id'])) {
            $auction = [
                'id' => (int)$row['auction_id'],
                'startPrice' => (int)($row['start_price'] ?? 0),
                'floorPrice' => (int)($row['floor_price'] ?? 0),
                'decreaseAmount' => (int)($row['decrease_amount'] ?? 0),
                'stepMinutes' => (int)($row['step_minutes'] ?? 0),
                'startedAt' => $row['started_at'] ?? null,
                'status' => (string)($row['auction_status'] ?? ''),
                'currentPrice' => null,
                'isAtFloor' => null,
                'nextDropInSeconds' => null,
            ];

            if (($row['auction_status'] ?? '') === 'active' && !empty($row['started_at'])) {
                $price = Auction::calculateCurrentPrice($row);
                $auction['currentPrice'] = $price['current_price'];
                $auction['isAtFloor'] = $price['is_at_floor'];
                $auction['nextDropInSeconds'] = $price['next_drop_in_seconds'];
                $dto['currentPrice'] = $price['current_price'];
                $dto['isAtFloor'] = $price['is_at_floor'];
            }
            $dto['auction'] = $auction;
        }

        return $dto;
    }

    /** @return array<string, mixed> */
    private function categoryDto(array $row): array
    {
        return [
            'id' => (int)$row['id'],
            'name' => (string)($row['name'] ?? ''),
            'slug' => (string)($row['slug'] ?? ''),
            'icon' => (string)($row['icon'] ?? ''),
            'createdAt' => $row['created_at'] ?? null,
        ];
    }

    /** @return array<string, mixed> */
    private function leaderboardDto(array $row): array
    {
        $id = (int)$row['id'];
        return [
            'id' => $id,
            'name' => (string)($row['name'] ?? ''),
            'avatarUrl' => !empty($row['avatar'])
                ? $this->publicUrl('/users/avatar?id=' . $id)
                : ($row['avatar_url'] ?? null),
            'isStudentVerified' => (bool)($row['is_student_verified'] ?? false),
            'productCount' => (int)($row['product_count'] ?? 0),
            'soldCount' => (int)($row['sold_count'] ?? 0),
            'avgRating' => (float)($row['avg_rating'] ?? 0),
            'ratingCount' => (int)($row['rating_count'] ?? 0),
            'score' => (float)($row['score'] ?? 0),
        ];
    }

    /** @return array<string, mixed> */
    private function bannerDto(array $row): array
    {
        $id = (int)$row['id'];
        return [
            'id' => $id,
            'title' => (string)($row['title'] ?? ''),
            'imageUrl' => !empty($row['image']) ? $this->publicUrl('/banners/image?id=' . $id) : null,
            'link' => (string)($row['link'] ?? ''),
            'isActive' => (bool)($row['is_active'] ?? false),
            'displayOrder' => (int)($row['display_order'] ?? 0),
            'createdAt' => $row['created_at'] ?? null,
        ];
    }

    /** @return array<string, mixed> */
    private function giveawayDto(array $row): array
    {
        $id = (int)$row['id'];
        $user = $_SESSION['user'] ?? null;
        $joinedByCurrentUser = is_array($user) && !empty($user['id'])
            ? $this->giveaways->hasJoined($id, (int)$user['id'])
            : false;

        return [
            'id' => $id,
            'title' => (string)($row['title'] ?? ''),
            'description' => (string)($row['description'] ?? ''),
            'imageUrl' => !empty($row['image']) ? $this->publicUrl('/giveaways/image?id=' . $id) : null,
            'endTime' => $row['end_time'] ?? null,
            'status' => (string)($row['status'] ?? ''),
            'winnerId' => isset($row['winner_id']) ? (int)$row['winner_id'] : null,
            'winnerName' => null,
            'createdAt' => $row['created_at'] ?? null,
            'participantCount' => $this->giveaways->countParticipants($id),
            'joinedByCurrentUser' => $joinedByCurrentUser,
        ];
    }

    private function publicUrl(string $path): string
    {
        return rtrim((string)($_ENV['APP_URL'] ?? ''), '/') . $path;
    }

    private function queryParameterString(string $name): string
    {
        $value = $_GET[$name] ?? '';
        if (!is_string($value) && !is_numeric($value)) {
            $this->validationError('Tham số "' . $name . '" không hợp lệ.');
        }
        return (string)$value;
    }

    private function queryParameterInt(string $name, int $default): int
    {
        if (!array_key_exists($name, $_GET)) {
            return $default;
        }
        $value = $_GET[$name];
        if (!is_string($value) && !is_int($value)) {
            $this->validationError('Tham số "' . $name . '" không hợp lệ.');
        }
        $parsed = filter_var($value, FILTER_VALIDATE_INT);
        if ($parsed === false) {
            $this->validationError('Tham số "' . $name . '" không hợp lệ.');
        }
        return $parsed;
    }
}
