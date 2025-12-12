<?php
class TestProductReview extends Model implements JsonSerializable
{
	public $id;
	public $product_id;
	public $user_id;
	public $rating;
	public $review;
	public $created_at;
	public $updated_at;

	public function __construct() {}

	/* 1. Check if user purchased the product */
	public static function userPurchasedProduct($user_id, $product_id)
	{
		global $db, $tx;

		// Convert product_id: handle 'p-4' or numeric
		if (is_string($product_id) && preg_match('/^p-(\d+)$/', $product_id, $matches)) {
			$numeric_id = (int)$matches[1];
		} else {
			$numeric_id = (int)$product_id;
		}

		// Query checks both possible formats
		$stmt = $db->prepare("
            SELECT COUNT(*) AS bought
            FROM {$tx}test_orders o
            JOIN {$tx}test_orderdetails d ON o.id = d.order_id
            WHERE o.user_id = ? 
              AND (d.product_id = ? OR d.product_id = CONCAT('p-', ?))
        ");
		$stmt->bind_param("iii", $user_id, $numeric_id, $numeric_id);
		$stmt->execute();
		$result = $stmt->get_result()->fetch_assoc();

		return $result['bought'] > 0;
	}

	/* 2. Save review */
	public function save()
	{
		global $db, $tx;

		// Convert product_id before saving
		if (is_string($this->product_id) && preg_match('/^p-(\d+)$/', $this->product_id, $matches)) {
			$this->product_id = (int)$matches[1];
		} else {
			$this->product_id = (int)$this->product_id;
		}

		if (!self::userPurchasedProduct($this->user_id, $this->product_id)) {
			return false;
		}

		$stmt = $db->prepare("
            INSERT INTO {$tx}test_product_reviews
            (product_id, user_id, rating, review)
            VALUES (?, ?, ?, ?)
        ");
		$stmt->bind_param(
			"iiis",
			$this->product_id,
			$this->user_id,
			$this->rating,
			$this->review
		);
		$stmt->execute();

		return $db->insert_id;
	}

	/* 3. Update review */
	public function update()
	{
		global $db, $tx;

		$stmt = $db->prepare("
            UPDATE {$tx}test_product_reviews
            SET rating=?, review=?, updated_at=NOW()
            WHERE id=?
        ");

		$stmt->bind_param(
			"isi",
			$this->rating,
			$this->review,
			$this->id
		);

		$stmt->execute();
	}

	/* 4. Average rating */
	public static function getAverageRating($product_id)
	{
		global $db, $tx;

		$stmt = $db->prepare("
            SELECT ROUND(AVG(rating),2) AS avg_rating
            FROM {$tx}test_product_reviews
            WHERE product_id = ?
        ");
		$stmt->bind_param("i", $product_id);
		$stmt->execute();

		$result = $stmt->get_result()->fetch_assoc();
		return $result['avg_rating'] ?? 0;
	}

	/* 5. Total reviews */
	public static function getTotalReviews($product_id)
	{
		global $db, $tx;

		$stmt = $db->prepare("
            SELECT COUNT(*) AS total_reviews
            FROM {$tx}test_product_reviews
            WHERE product_id = ?
        ");
		$stmt->bind_param("i", $product_id);
		$stmt->execute();

		$result = $stmt->get_result()->fetch_assoc();
		return $result['total_reviews'];
	}

	/* 6. All reviews */
	public static function getProductReviews($product_id)
	{
		global $db, $tx;

		$stmt = $db->prepare("
            SELECT *
            FROM {$tx}test_product_reviews
            WHERE product_id = ?
            ORDER BY created_at DESC
        ");

		$stmt->bind_param("i", $product_id);
		$stmt->execute();

		return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
	}

	/* Delete */
	public static function delete($id)
	{
		global $db, $tx;
		$db->query("DELETE FROM {$tx}test_product_reviews WHERE id={$id}");
	}

	public function jsonSerialize(): mixed
	{
		return get_object_vars($this);
	}
}
