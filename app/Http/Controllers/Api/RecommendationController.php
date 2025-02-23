<?php

namespace App\Http\Controllers\Api;

use App\Filament\Resources\ProductResource\Api\Transformers\ProductTransformer;
use App\Http\Resources\Api\Recommendation\QuestionResource;
use App\Models\Product;
use App\Models\RecommendationQuestion;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RecommendationController extends ApiController
{
    /**
     * Suggest products based on user answers.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function suggestion(Request $request)
    {

        if (! $request->has('userAnswers')) {
            return response()->json(['message' => 'no user answers found'], Response::HTTP_FORBIDDEN);
        }

        $userAnswers = $request->input('userAnswers', []);

        // Load all products with their answers
        $products = Product::with('answers')->get();

        // Map product answers
        $productAnswers = $products->mapWithKeys(function ($product) {
            return [$product->id => $product->answers->pluck('id')->toArray()];
        })->toArray();

        // Fetch question weights
        $questionWeights = RecommendationQuestion::pluck('weight', 'id')->toArray();

        // Calculate scores for each product
        $scores = [];
        foreach ($productAnswers as $productId => $answers) {
            $scores[$productId] = $this->calculateProductScore($userAnswers, $answers, $questionWeights);
        }

        // Sort scores in descending order and get top 5 product IDs
        ksort($scores);
        $topProductIds = array_slice(array_keys($scores), 0, 5);

        // Load detailed product information for top products
        $recommendedProducts = Product::whereIn('id', $topProductIds)
            ->with([
                'media',
                'category',
                'attributes',
                'comments.user',
                'comments.replies',
                'seo',
                'prices.specialPrices',
                'faqs',
            ])->get();

        // Transform and return the response
        return response()->json([
            'scores' => $scores,
            'products' => ProductTransformer::collection($recommendedProducts),
        ], Response::HTTP_OK);
    }

    /**
     * Suggest products based on user answers.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getQuestion()
    {
        $questions = RecommendationQuestion::all()->load(['answers']);

        return $this->dataResponse(QuestionResource::collection($questions), Response::HTTP_OK);
    }

    /**
     * Calculate the score for a product based on user answers and question weights.
     */
    private function calculateProductScore(array $userAnswers, array $productAnswers, array $questionWeights): float
    {
        $totalScore = 0;
        foreach ($userAnswers as $questionId => $userAnswerSet) {
            // Skip if the question has no defined weight
            if (! isset($questionWeights[$questionId])) {
                continue;
            }

            $weight = $questionWeights[$questionId];

            // Ensure user answers are an array
            $userAnswerSet = is_array($userAnswerSet) ? $userAnswerSet : [$userAnswerSet];

            // Find matching answers between user and product
            $matches = array_intersect($userAnswerSet, $productAnswers);

            // Add weight to total score if matches exist
            if (! empty($matches)) {
                $totalScore += $weight;
            }
        }

        // Normalize the score to a range of 0 to 1
        return $totalScore;
    }
}
