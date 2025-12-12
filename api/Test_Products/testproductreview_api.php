<?php
class TestProductReviewApi
{
	public function __construct() {}
	function index($id)
	{
		echo json_encode(["average_rating" => TestProductReview::getAverageRating($id)]);
	}

	function delete($data)
	{
		TestProductReview::delete($data["id"]);
		echo json_encode(["success" => "yes"]);
	}
	function save($data, $file = [])
	{
		$testproductreview = new TestProductReview();
		$testproductreview->product_id = $data["product_id"];
		$testproductreview->user_id = $data["user_id"];
		$testproductreview->rating = $data["rating"];
		$testproductreview->review = $data["review"];

		$id = $testproductreview->save();

		echo json_encode([
			'data' => $data,
			"success" => $id ? "yes" : "no",
			"id" => $id
		]);
	}

	function update($data, $file = [])
	{
		$testproductreview = new TestProductReview();
		$testproductreview->id = $data["id"];
		$testproductreview->rating = $data["rating"];
		$testproductreview->review = $data["review"];

		$testproductreview->update();

		echo json_encode(["success" => "yes"]);
	}
}
