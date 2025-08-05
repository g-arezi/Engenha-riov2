<?php
namespace src\Services;

/**
 * Service for managing login slides
 */
class LoginSlideService {
    private $dataFile;
    private $uploadsDir;

    public function __construct() {
        $this->dataFile = __DIR__ . '/../../data/login_slides.json';
        $this->uploadsDir = __DIR__ . '/../../public/assets/images/login/';

        // Create uploads directory if it doesn't exist
        if (!file_exists($this->uploadsDir)) {
            mkdir($this->uploadsDir, 0755, true);
        }
    }

    /**
     * Get all slides
     *
     * @return array All slides data
     */
    public function getAllSlides() {
        if (file_exists($this->dataFile)) {
            $slidesJson = file_get_contents($this->dataFile);
            return json_decode($slidesJson, true) ?: [];
        }
        return [];
    }

    /**
     * Get only active slides
     *
     * @return array Active slides data
     */
    public function getActiveSlides() {
        $slides = $this->getAllSlides();
        return array_filter($slides, function($slide) {
            return isset($slide['active']) && $slide['active'] === true;
        });
    }

    /**
     * Get slide by ID
     *
     * @param string $id Slide ID
     * @return array|null Slide data or null if not found
     */
    public function getSlideById($id) {
        $slides = $this->getAllSlides();
        foreach ($slides as $slide) {
            if ($slide['id'] === $id) {
                return $slide;
            }
        }
        return null;
    }

    /**
     * Create a new slide
     *
     * @param array $data Slide data
     * @param array $file Optional file data for upload
     * @return string|bool New slide ID or false on failure
     */
    public function createSlide($data, $file = null) {
        $slides = $this->getAllSlides();
        
        // Generate a new unique ID
        $id = uniqid();
        
        $newSlide = [
            'id' => $id,
            'type' => $data['type'],
            'title' => $data['title'] ?? '',
            'description' => $data['description'] ?? '',
            'active' => isset($data['active']) ? true : false,
            'created_at' => date('Y-m-d H:i:s')
        ];

        // Handle different slide types
        if ($data['type'] === 'image' && $file) {
            // Process and save uploaded file
            $fileResult = $this->handleImageUpload($file, $id);
            if (!$fileResult) {
                return false;
            }
            $newSlide['url'] = $fileResult;
        } elseif ($data['type'] === 'color') {
            // For color slides, the URL is the color value
            $newSlide['url'] = $data['color'] ?? '#2c3e50';
        } else {
            return false;
        }

        // Add new slide to collection
        $slides[] = $newSlide;
        
        // Save updated collection
        if ($this->saveSlides($slides)) {
            return $id;
        }
        
        return false;
    }

    /**
     * Update an existing slide
     *
     * @param string $id Slide ID to update
     * @param array $data Updated slide data
     * @param array $file Optional file data for upload
     * @return bool Success status
     */
    public function updateSlide($id, $data, $file = null) {
        $slides = $this->getAllSlides();
        
        // Find the slide to update
        $slideIndex = -1;
        foreach ($slides as $index => $slide) {
            if ($slide['id'] === $id) {
                $slideIndex = $index;
                break;
            }
        }

        if ($slideIndex === -1) {
            return false;
        }

        // Prepare updated slide data
        $updatedSlide = $slides[$slideIndex];
        
        // Update basic information
        $updatedSlide['title'] = $data['title'] ?? $updatedSlide['title'];
        $updatedSlide['description'] = $data['description'] ?? $updatedSlide['description'];
        $updatedSlide['active'] = isset($data['active']) ? true : false;
        $updatedSlide['updated_at'] = date('Y-m-d H:i:s');

        // Handle type change or content update
        if (isset($data['type'])) {
            $updatedSlide['type'] = $data['type'];
            
            if ($data['type'] === 'image') {
                // If there's a new file, update the image
                if ($file && !empty($file['tmp_name'])) {
                    // Delete previous image if exists and is not the same as default
                    if ($updatedSlide['type'] === 'image' && isset($updatedSlide['url'])) {
                        $this->deleteImageFile($updatedSlide['url']);
                    }
                    
                    // Process and save new uploaded file
                    $fileResult = $this->handleImageUpload($file, $id);
                    if (!$fileResult) {
                        return false;
                    }
                    $updatedSlide['url'] = $fileResult;
                }
            } elseif ($data['type'] === 'color') {
                // For color slides, update the color value
                $updatedSlide['url'] = $data['color'] ?? '#2c3e50';
                
                // Delete previous image if exists
                if (isset($slides[$slideIndex]['url']) && $slides[$slideIndex]['type'] === 'image') {
                    $this->deleteImageFile($slides[$slideIndex]['url']);
                }
            }
        }

        // Update the slide in the collection
        $slides[$slideIndex] = $updatedSlide;
        
        // Save updated collection
        return $this->saveSlides($slides);
    }

    /**
     * Delete a slide
     *
     * @param string $id Slide ID to delete
     * @return bool Success status
     */
    public function deleteSlide($id) {
        $slides = $this->getAllSlides();
        
        // Find the slide to delete
        $slideIndex = -1;
        foreach ($slides as $index => $slide) {
            if ($slide['id'] === $id) {
                $slideIndex = $index;
                break;
            }
        }

        if ($slideIndex === -1) {
            return false;
        }

        // Delete associated image file if exists
        if ($slides[$slideIndex]['type'] === 'image') {
            $this->deleteImageFile($slides[$slideIndex]['url']);
        }

        // Remove slide from collection
        array_splice($slides, $slideIndex, 1);
        
        // Save updated collection
        return $this->saveSlides($slides);
    }

    /**
     * Save slides data to JSON file
     *
     * @param array $slides Slides data to save
     * @return bool Success status
     */
    private function saveSlides($slides) {
        $slidesJson = json_encode($slides, JSON_PRETTY_PRINT);
        return file_put_contents($this->dataFile, $slidesJson) !== false;
    }

    /**
     * Handle image file upload
     *
     * @param array $file File upload data
     * @param string $id Slide ID for filename
     * @return string|false URL path on success, false on failure
     */
    private function handleImageUpload($file, $id) {
        // Validate file
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            return false;
        }

        // Create a unique filename
        $fileExt = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = $id . '.' . $fileExt;
        $targetPath = $this->uploadsDir . $filename;

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return '/assets/images/login/' . $filename;
        }

        return false;
    }

    /**
     * Delete an image file
     *
     * @param string $url URL path of image to delete
     * @return bool Success status
     */
    private function deleteImageFile($url) {
        // Extract filename from URL
        $filename = basename($url);
        $filepath = $this->uploadsDir . $filename;
        
        // Delete file if exists
        if (file_exists($filepath)) {
            return unlink($filepath);
        }
        
        return false;
    }
}
