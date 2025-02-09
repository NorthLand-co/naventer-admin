<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Intervention\Image\Facades\Image;
use Spatie\ImageOptimizer\OptimizerChainFactory;
use Symfony\Component\HttpFoundation\Response;

class ImageController extends Controller
{
    protected $guzzleClient;

    protected $cacheDuration = 86400; // 1 year in seconds

    public function __construct()
    {
        // Initialize the Guzzle HTTP client
        $this->guzzleClient = new Client;
    }

    public function optimize(Request $request)
    {

        // Future: Validate the request signature (for security)
        // if (!$this->isValidSignature($request)) {
        //     return response()->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        // }

        // Step 1: Validate URL and other inputs
        $validated = $request->validate([
            'url' => 'required|url',
            'width' => 'nullable|integer|min:50|max:2000',
            'height' => 'nullable|integer|min:50|max:2000',
            'quality' => 'nullable|integer|min:10|max:100',
            'format' => 'nullable|string|in:jpg,png,webp',
            'blur' => 'nullable|integer|min:1|max:100',
            'grayscale' => 'nullable|boolean',
            'brightness' => 'nullable|integer|min:-100|max:100',
            'contrast' => 'nullable|integer|min:-100|max:100',
            'invert' => 'nullable|boolean',
        ]);

        // Generate a cache key based on the URL and transformation options
        $cacheKey = md5($request->getUri().json_encode($validated));

        // Check if the processed image is already cached
        if (Cache::has($cacheKey)) {
            return response(Cache::get($cacheKey), Response::HTTP_OK)
                ->header('Content-Type', Cache::get("{$cacheKey}_mime"))
                ->header('Cache-Control', "public, max-age={$this->cacheDuration}")
                ->header('Expires', gmdate('D, d M Y H:i:s', time() + $this->cacheDuration).' GMT');
        }

        try {
            // Step 2: Fetch the image asynchronously using Guzzle
            $response = $this->guzzleClient->get($validated['url'], [
                'timeout' => 5, // Timeout in seconds
                'verify' => env('APP_ENV') !== 'local',
            ]);

            if ($response->getStatusCode() !== 200) {
                return response()->json(['error' => 'Image not found'], Response::HTTP_NOT_FOUND);
            }

            $imageContent = $response->getBody()->getContents();

            // Step 3: Use Intervention Image to resize and optimize the image
            $image = Image::make($imageContent)
                ->resize(
                    $validated['width'] ?? 300,
                    $validated['height'] ?? 300,
                    function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    }
                );

            // Apply optional filters based on request
            if (isset($validated['blur'])) {
                $image->blur($validated['blur']);
            }

            if (isset($validated['grayscale']) && $validated['grayscale']) {
                $image->greyscale();
            }

            if (isset($validated['brightness'])) {
                $image->brightness($validated['brightness']);
            }

            if (isset($validated['contrast'])) {
                $image->contrast($validated['contrast']);
            }

            if (isset($validated['invert']) && $validated['invert']) {
                $image->invert();
            }

            // Determine the format to output, using WebP by default if the client supports it
            $outputFormat = $validated['format'] ?? (strpos($request->header('Accept'), 'image/webp') !== false ? 'webp' : 'jpg');
            $image->encode($outputFormat, $validated['quality'] ?? 80);

            // Save the image temporarily to the disk
            $tempDir = storage_path('app/temp/');
            if (! is_dir($tempDir)) {
                mkdir($tempDir, 0775, true);
            }
            $tempFilePath = $tempDir.uniqid().'.'.$outputFormat;
            $image->save($tempFilePath);

            // Optimize the image using Spatie's optimizer
            $optimizerChain = OptimizerChainFactory::create();
            $optimizerChain->optimize($tempFilePath);

            // Load the optimized image back into memory
            $optimizedImage = file_get_contents($tempFilePath);

            // Cache the optimized image
            Cache::put($cacheKey, $optimizedImage, now()->addMonth());
            Cache::put("{$cacheKey}_mime", $image->mime(), now()->addMonth());

            // Delete the temporary file
            unlink($tempFilePath);

            // Step 6: Return the optimized image with proper headers
            return response($image->encoded, Response::HTTP_OK)
                ->header('Content-Type', $image->mime())
                ->header('Cache-Control', "public, max-age={$this->cacheDuration}")
                ->header('Expires', gmdate('D, d M Y H:i:s', time() + $this->cacheDuration).' GMT');
        } catch (\Exception $e) {
            return response()->json(['error' => 'Image processing failed'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
