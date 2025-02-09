<?php

use Illuminate\Support\Facades\Storage;

if (! function_exists('camelToSnakeCase')) {
    function camelToSnakeCase(string $input): string
    {
        $pattern = '/([a-z])([A-Z])/';
        $replacement = '$1_$2';

        return strtolower(preg_replace($pattern, $replacement, $input));
    }
}

if (! function_exists('compareWithOperand')) {
    /**
     * Compare the product attribute with the given condition value and operand.
     *
     * @param  mixed  $productValue  The value of the product attribute.
     * @param  string  $operand  The operand (e.g., '>=', '<=', 'in').
     * @param  mixed  $conditionValue  The value in the condition to compare against.
     * @return bool Whether the condition is met.
     */
    function compareWithOperand($productValue, $operand, $conditionValue)
    {
        switch ($operand) {
            case '>=':
                return $productValue >= $conditionValue;
            case '<=':
                return $productValue <= $conditionValue;
            case '>':
                return $productValue > $conditionValue;
            case '<':
                return $productValue < $conditionValue;
            case 'in':
                return in_array($productValue, $conditionValue);
            case '=':
                return $productValue == $conditionValue;
            default:
                return false;
        }
    }
}

if (! function_exists('saveBase64Image')) {
    function saveBase64Image($base64Image, $path, $name)
    {
        // Step 1: Remove the prefix
        if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
            $base64Image = substr($base64Image, strpos($base64Image, ',') + 1);
            $type = strtolower($type[1]); // jpg, png, gif

            // Check if the image type is valid
            if (! in_array($type, ['jpg', 'jpeg', 'png', 'gif'])) {
                throw new \Exception('Invalid image type.');
            }

            // Step 2: Decode the Base64 string
            $base64Image = base64_decode($base64Image);

            if ($base64Image === false) {
                throw new \Exception('Base64 decode failed.');
            }

            // Step 3: Store the image
            $filePath = $path.$name.'.'.$type; // Define your storage path and file name
            Storage::disk('s3')->put($path, $base64Image); // Store the image in the 'public' disk

            return $filePath; // Return the path of the saved image
        }

        throw new \Exception('Invalid Base64 string.');
    }
}
