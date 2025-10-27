<?php

namespace App\Services;

use CoreConstants;
use App\Models\Project;
use App\Models\About;
use App\Services\Contracts\ProjectInterface;
use App\Services\ImageOptimizationService;
use Illuminate\Http\UploadedFile;
use Log;
use Str;
use Validator;
use Barryvdh\DomPDF\Facade\Pdf;

class ProjectService implements ProjectInterface
{
    /**
     * Eloquent instance
     *
     * @var Project
     */
    private $model;

    /**
     * Create a new service instance
     *
     * @param Project $project
     * @return void
     */
    public function __construct(Project $project)
    {
        $this->model = $project;
    }

    /**
     * Get all fields
     *
     * @param array $select
     * @return array
     */
    public function getAll(array $select = ['*'])
    {
        try {
            $result = $this->model->select($select)->get();
            if ($result) {
                return [
                    'message' => 'Data is fetched successfully',
                    'payload' => $result,
                    'status' => CoreConstants::STATUS_CODE_SUCCESS
                ];
            } else {
                return [
                    'message' => 'No result found',
                    'payload' => null,
                    'status' => CoreConstants::STATUS_CODE_NOT_FOUND
                ];
            }
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return [
                'message' => 'Something went wrong',
                'payload' => $th->getMessage(),
                'status' => CoreConstants::STATUS_CODE_ERROR
            ];
        }
    }

    /**
     * Store/update data
     *
     * @param array $data
     * @return array
     */
    public function store(array $data)
    {
        try {
            if (isset($data['seeder_thumbnail']) && isset($data['seeder_images'])) {
                $validate = Validator::make($data, [
                    'title' => 'required|string',
                    'categories' => 'required'
                ]);
            } else {
                $validate = Validator::make($data, [
                    'title' => 'required|string',
                    'thumbnail' => 'required',
                    'images' => 'required',
                    'categories' => 'required'
                ]);
            }

            if ($validate->fails()) {
                return [
                    'message' => 'Validation Error',
                    'payload' => $validate->errors(),
                    'status' => CoreConstants::STATUS_CODE_BAD_REQUEST
                ];
            }

            $newData['title'] = $data['title'];
            $newData['categories'] = json_encode($data['categories']);
            $newData['link'] = isset($data['link']) ? $data['link'] : null;
            $newData['details'] = isset($data['details']) ? $data['details'] : null;

            if (isset($data['seeder_thumbnail']) && isset($data['seeder_images'])) {
                $newData['thumbnail'] = $data['seeder_thumbnail'];
                $newData['images'] = json_encode($data['seeder_images']);
                $result = $this->model->create($newData);
            } else {
                if (!empty($data['id'])) {
                    $result = $this->getById($data['id'], ['*']);
                    if ($result['status'] !== CoreConstants::STATUS_CODE_SUCCESS) {
                        return $result;
                    } else {
                        $existingData = $result['payload'];
                    }

                    //process thumbnail
                    $processThumbnail = $this->processThumbnail($data['thumbnail'], $existingData);
                    if ($processThumbnail['status'] !== CoreConstants::STATUS_CODE_SUCCESS) {
                        return $processThumbnail;
                    }
                    //process images
                    $processImages = $this->processImages($data['images'], $existingData);
                    if ($processImages['status'] !== CoreConstants::STATUS_CODE_SUCCESS) {
                        return $processImages;
                    }
                    $newData['thumbnail'] = $processThumbnail['payload']['file'];
                    $newData['images'] = json_encode($processImages['payload']['files']);

                    $result = $existingData->update($newData);
                } else {
                    //process thumbnail
                    $processThumbnail = $this->processThumbnail($data['thumbnail']);
                    if ($processThumbnail['status'] !== CoreConstants::STATUS_CODE_SUCCESS) {
                        return $processThumbnail;
                    }

                    //process images
                    $processImages = $this->processImages($data['images']);
                    if ($processImages['status'] !== CoreConstants::STATUS_CODE_SUCCESS) {
                        return $processImages;
                    }

                    $newData['thumbnail'] = $processThumbnail['payload']['file'];
                    $newData['images'] = json_encode($processImages['payload']['files']);
                    $result = $this->model->create($newData);
                }
            }

            if ($result) {
                return [
                    'message' => isset($data['id']) ? 'Data is successfully updated' : 'Data is successfully saved',
                    'payload' => $result,
                    'status' => CoreConstants::STATUS_CODE_SUCCESS
                ];
            } else {
                return [
                    'message' => 'Something went wrong',
                    'payload' => null,
                    'status' => CoreConstants::STATUS_CODE_ERROR
                ];
            }
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return [
                'message' => 'Something went wrong',
                'payload' => $th->getMessage(),
                'status' => CoreConstants::STATUS_CODE_ERROR
            ];
        }
    }

    /**
     * Process the thumbnail
     *
     * @param UploadedFile $file
     * @param Project|null $project
     * @return array
     */
    private function processThumbnail(UploadedFile $file, $project = null)
    {
        if ($project) {
            //delete previous
            try {
                if (file_exists($project->thumbnail)) {
                    unlink($project->thumbnail);
                }
            } catch (\Throwable $th) {
                Log::error($th->getMessage());
            }
        }
        //new entry
        try {
            $fileName = time().'_'.Str::random(10).'.png';
            $pathName = 'assets/common/img/projects/';
            
            if (!file_exists($pathName)) {
                mkdir($pathName, 0777, true);
            }
            if ($file->move($pathName, $fileName)) {
                $fullPath = $pathName.$fileName;
                
                // Optimize the thumbnail
                $imageOptimizer = new ImageOptimizationService();
                $optimizationResult = $imageOptimizer->optimizeProjectThumbnail($fullPath);
                
                if ($optimizationResult['status']) {
                    Log::info('Project thumbnail optimized', [
                        'original' => $fullPath,
                        'webp' => $optimizationResult['webp_path'],
                        'savings' => $optimizationResult['savings_percent'] . '%'
                    ]);
                }
                
                return [
                    'message' => __('services.file_saved_successfully'),
                    'payload' => [
                        'file' => $fullPath
                    ],
                    'status' => CoreConstants::STATUS_CODE_SUCCESS
                ];
            } else {
                return [
                    'message' => 'File could not be saved',
                    'payload' => null,
                    'status' => CoreConstants::STATUS_CODE_ERROR
                ];
            }
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return [
                'message' => 'Something went wrong',
                'payload' => $th->getMessage(),
                'status'  => CoreConstants::STATUS_CODE_ERROR
            ];
        }
    }

    /**
     * Process the image file array
     *
     * @param array $fileArray
     * @param Project|null $project
     * @return array
     */
    private function processImages(Array $fileArray, $project = null)
    {
        if ($project) {
            //delete previous
            try {
                $existingImages = json_decode($project->images, true);
                foreach ($existingImages as $key => $existingImage) {
                    if (file_exists($existingImage)) {
                        unlink($existingImage);
                    }
                }
            } catch (\Throwable $th) {
                Log::error($th->getMessage());
            }
        }
        //new entry
        try {
            $savedFileArray = [];
            foreach ($fileArray as $key => $file) {
                try {
                    $fileName = time().'_'.Str::random(10).'.png';
                    $pathName = 'assets/common/img/projects/';
                    
                    if (!file_exists($pathName)) {
                        mkdir($pathName, 0777, true);
                    }
                    if ($file->move($pathName, $fileName)) {
                        $fullPath = $pathName.$fileName;
                        
                        // Optimize the image
                        $imageOptimizer = new ImageOptimizationService();
                        $optimizationResult = $imageOptimizer->optimizeProjectImage($fullPath);
                        
                        if ($optimizationResult['status']) {
                            Log::info('Project image optimized', [
                                'original' => $fullPath,
                                'webp' => $optimizationResult['webp_path'],
                                'savings' => $optimizationResult['savings_percent'] . '%'
                            ]);
                        }
                        
                        array_push($savedFileArray, $fullPath);
                    }
                } catch (\Throwable $th) {
                    Log::error($th->getMessage());
                }
            }

            if (count($savedFileArray)) {
                return [
                    'message' => __('services.files_saved_successfully'),
                    'payload' => [
                        'files' => $savedFileArray
                    ],
                    'status' => CoreConstants::STATUS_CODE_SUCCESS
                ];
            } else {
                return [
                    'message' => __('services.no_file_could_be_saved'),
                    'payload' => null,
                    'status' => CoreConstants::STATUS_CODE_ERROR
                ];
            }
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return [
                'message' => 'Something went wrong',
                'payload' => $th->getMessage(),
                'status'  => CoreConstants::STATUS_CODE_ERROR
            ];
        }
    }

    /**
     * Fetch item by id
     *
     * @param int $id
     * @param array $select
     * @return array
     */
    public function getById(int $id, array $select = ['*'])
    {
        try {
            $data = $this->model->select($select)->where('id', $id)->first();
            
            if ($data) {
                return [
                    'message' => 'Data is fetched successfully',
                    'payload' => $data,
                    'status' => CoreConstants::STATUS_CODE_SUCCESS
                ];
            } else {
                return [
                    'message' => 'No result is found',
                    'payload' => null,
                    'status' => CoreConstants::STATUS_CODE_NOT_FOUND
                ];
            }
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return [
                'message' => 'Something went wrong',
                'payload' => $th->getMessage(),
                'status' => CoreConstants::STATUS_CODE_ERROR
            ];
        }
    }

    /**
     * Get all fields with paginate
     *
     * @param array $data
     * @param array $select
     * @return array
     */
    public function getAllWithPaginate(array $data, array $select = ['*'])
    {
        try {
            $perPage  = !empty($data['params']) && !empty(json_decode($data['params'])->pageSize) ? json_decode($data['params'])->pageSize : 10;
            
            if (!empty($data['sorter']) && count(json_decode($data['sorter'], true))) {
                $sorter = json_decode($data['sorter'], true);
                foreach ($sorter as $key => $value) {
                    $sortBy = $key;
                    $sortType = ($value === 'ascend' ? 'asc' : 'desc');
                }
            } else {
                $sortBy = 'created_at';
                $sortType = 'desc';
            }
            
            $result = $this->model->select($select)->orderBy($sortBy, $sortType);

            if (!empty($data['params']) && !empty(json_decode($data['params'])->keyword) && json_decode($data['params'])->keyword !== '') {
                $searchQuery = json_decode($data['params'])->keyword;
                $columns = !empty($data['columns']) ? $data['columns'] : null;
                
                if ($columns) {
                    $result->where(function ($query) use ($columns, $searchQuery) {
                        foreach ($columns as $key => $column) {
                            if (!empty(json_decode($column)->search) && json_decode($column)->search === true) {
                                $fieldName = json_decode($column)->dataIndex;
                                $query->orWhere($fieldName, 'like', '%' . $searchQuery . '%');
                            }
                        }
                    });
                }
            }

            $result = $result->paginate($perPage);
            
            if ($result) {
                return [
                    'message' => 'Data is fetched successfully',
                    'payload' => $result,
                    'status' => CoreConstants::STATUS_CODE_SUCCESS
                ];
            } else {
                return [
                    'message' => 'No result found',
                    'payload' => null,
                    'status' => CoreConstants::STATUS_CODE_NOT_FOUND
                ];
            }
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return [
                'message' => 'Something went wrong',
                'payload' => $th->getMessage(),
                'status' => CoreConstants::STATUS_CODE_ERROR
            ];
        }
    }

    /**
     * Delete items by id array
     *
     * @param array $ids
     * @return array
     */
    public function deleteByIds(array $ids)
    {
        try {
            $entries = $this->model->whereIn('id', $ids)->get();
            $deleted = 0;

            foreach ($entries as $key => $entry) {
                //delete thumbnail
                try {
                    if (file_exists($entry->thumbnail)) {
                        unlink($entry->thumbnail);
                    }
                } catch (\Throwable $th) {
                    Log::error($th->getMessage());
                }
                //delete images
                try {
                    $existingImages = json_decode($entry->images, true);
                    foreach ($existingImages as $key => $existingImage) {
                        if (file_exists($existingImage)) {
                            unlink($existingImage);
                        }
                    }
                } catch (\Throwable $th) {
                    Log::error($th->getMessage());
                }
                $entry->delete();
                $deleted++;
            }

            if ($deleted) {
                return [
                    'message' => 'Data is deleted successfully',
                    'payload' => [
                        'totalDeleted' => $deleted
                    ],
                    'status'  => CoreConstants::STATUS_CODE_SUCCESS
                ];
            } else {
                return [
                    'message' => 'Nothing to Delete',
                    'payload' => null,
                    'status'  => CoreConstants::STATUS_CODE_NOT_FOUND
                ];
            }
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return [
                'message' => 'Something went wrong',
                'payload' => $th->getMessage(),
                'status' => CoreConstants::STATUS_CODE_ERROR
            ];
        }
    }

    /**
     * Export projects to PDF
     *
     * @return array
     */
    public function exportToPDF()
    {
        try {
            $projects = $this->model->orderBy('created_at', 'desc')->get();
            
            if ($projects->isEmpty()) {
                return [
                    'message' => 'No projects found to export',
                    'payload' => null,
                    'status' => CoreConstants::STATUS_CODE_NOT_FOUND
                ];
            }

            // Get author information
            $about = About::first();
            $authorName = $about ? $about->name : 'Portfolio Owner';

            $html = $this->generateProjectsHTML($projects, $authorName);
            
            $pdf = Pdf::loadHTML($html);
            $pdf->setPaper('A4', 'portrait');
            $pdf->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'DejaVu Sans',
                'isUnicode' => true,
                'isPhpEnabled' => true,
                'isJavascriptEnabled' => false,
                'fontHeightRatio' => 1.1,
                'isFontSubsettingEnabled' => true,
                'defaultMediaType' => 'print'
            ]);
            
            return [
                'message' => 'PDF generated successfully',
                'payload' => $pdf->output(),
                'status' => CoreConstants::STATUS_CODE_SUCCESS
            ];
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return [
                'message' => 'Something went wrong while generating PDF',
                'payload' => $th->getMessage(),
                'status' => CoreConstants::STATUS_CODE_ERROR
            ];
        }
    }

    /**
     * Generate HTML content for projects PDF
     *
     * @param \Illuminate\Database\Eloquent\Collection $projects
     * @param string $authorName
     * @return string
     */
    private function generateProjectsHTML($projects, $authorName)
    {
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Portfolio Projects</title>
            <style>
                body {
                    font-family: "DejaVu Sans", Arial, sans-serif;
                    margin: 0;
                    padding: 20px;
                    color: #333;
                    background-color: #f8f9fa;
                }
                .container {
                    max-width: 800px;
                    margin: 0 auto;
                    background-color: white;
                    padding: 30px;
                    border-radius: 12px;
                    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                }
                .header {
                    text-align: center;
                    margin-bottom: 40px;
                    border-bottom: 3px solid #007bff;
                    padding-bottom: 25px;
                }
                .header h1 {
                    color: #007bff;
                    margin: 0;
                    font-size: 32px;
                    font-weight: 700;
                }
                .header .author {
                    color: #666;
                    font-size: 16px;
                    margin-top: 10px;
                    font-weight: 500;
                }
                .header .date {
                    color: #888;
                    font-size: 14px;
                    margin-top: 5px;
                }
                .project {
                    margin-bottom: 35px;
                    padding: 25px;
                    border: 1px solid #e9ecef;
                    border-radius: 12px;
                    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
                    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
                    transition: transform 0.2s ease;
                }
                .project-title {
                    font-size: 22px;
                    font-weight: 600;
                    color: #007bff;
                    margin-bottom: 15px;
                    border-left: 4px solid #007bff;
                    padding-left: 15px;
                }
                .project-details {
                    margin: 15px 0;
                    line-height: 1.7;
                    color: #555;
                    font-size: 15px;
                }
                .project-link {
                    margin: 15px 0;
                }
                .project-link a {
                    color: #007bff;
                    text-decoration: none;
                    font-weight: 500;
                    border-bottom: 1px dotted #007bff;
                }
                .project-images {
                    margin: 20px 0;
                    display: flex;
                    flex-wrap: wrap;
                    gap: 10px;
                }
                .project-image {
                    width: 120px;
                    height: 80px;
                    object-fit: cover;
                    border-radius: 8px;
                    border: 2px solid #e9ecef;
                }
                .categories {
                    margin: 20px 0 0 0;
                    clear: both;
                }
                .categories-label {
                    font-weight: 600;
                    color: #495057;
                    margin-bottom: 10px;
                    font-size: 14px;
                }
                .category-tag {
                    display: inline-block;
                    background: linear-gradient(135deg, #007bff, #0056b3);
                    color: white;
                    padding: 6px 12px;
                    border-radius: 20px;
                    margin-right: 8px;
                    margin-bottom: 8px;
                    font-size: 12px;
                    font-weight: 500;
                    box-shadow: 0 2px 4px rgba(0, 123, 255, 0.3);
                }
                .no-projects {
                    text-align: center;
                    color: #666;
                    font-style: italic;
                    margin-top: 50px;
                    font-size: 18px;
                }
                .footer {
                    margin-top: 40px;
                    text-align: center;
                    color: #888;
                    font-size: 12px;
                    border-top: 1px solid #e9ecef;
                    padding-top: 20px;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>Портфолио проектов</h1>
                    <div class="author">Автор: ' . htmlspecialchars($authorName) . '</div>
                    <div class="date">Создано: ' . date('d.m.Y') . '</div>
                </div>';

        if ($projects->count() > 0) {
            foreach ($projects as $project) {
                $categories = json_decode($project->categories, true) ?? [];
                $images = json_decode($project->images, true) ?? [];
                
                $categoryTags = '';
                foreach ($categories as $category) {
                    $categoryTags .= '<span class="category-tag">' . htmlspecialchars($category) . '</span>';
                }

                $imagesHtml = '';
                if (!empty($images)) {
                    $imagesHtml = '<div class="project-images">';
                    foreach (array_slice($images, 0, 6) as $image) { // Показываем максимум 6 изображений
                        if (file_exists($image) && is_readable($image)) {
                            try {
                                $imageData = base64_encode(file_get_contents($image));
                                $imageExtension = strtolower(pathinfo($image, PATHINFO_EXTENSION));
                                $mimeType = 'image/' . ($imageExtension === 'jpg' ? 'jpeg' : $imageExtension);
                                $imagesHtml .= '<img src="data:' . $mimeType . ';base64,' . $imageData . '" class="project-image" alt="Project Image">';
                            } catch (\Exception $e) {
                                Log::error('Error processing image for PDF: ' . $e->getMessage());
                            }
                        }
                    }
                    $imagesHtml .= '</div>';
                }

                $html .= '
                <div class="project">
                    <div class="project-title">' . htmlspecialchars($project->title) . '</div>
                    
                    ' . ($project->details ? '<div class="project-details"><strong>Описание:</strong> ' . htmlspecialchars($project->details) . '</div>' : '') . '
                    
                    ' . ($project->link ? '<div class="project-link"><strong>Ссылка:</strong> <a href="' . htmlspecialchars($project->link) . '">' . htmlspecialchars($project->link) . '</a></div>' : '') . '
                    
                    ' . $imagesHtml . '
                    
                    ' . (!empty($categories) ? '<div class="categories"><div class="categories-label">Категории:</div>' . $categoryTags . '</div>' : '') . '
                </div>';
            }
        } else {
            $html .= '<div class="no-projects">Проекты не найдены</div>';
        }

        $html .= '
                <div class="footer">
                    <p>Документ создан автоматически системой управления портфолио</p>
                </div>
            </div>
        </body>
        </html>';

        return $html;
    }
}
