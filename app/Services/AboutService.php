<?php

namespace App\Services;

use CoreConstants;
use App\Models\About;
use App\Services\Contracts\AboutInterface;
use App\Support\LocaleContent;
use Log;
use Str;
use Validator;

class AboutService implements AboutInterface
{
    /**
     * Eloquent instance
     *
     * @var About
     */
    private $model;

    /**
     * Create a new service instance
     *
     * @param About $about
     * @return void
     */
    public function __construct(About $about)
    {
        $this->model = $about;
    }

    /**
     * Get all about fields
     *
     * @param array $select
     * @return array
     */
    public function getAll(array $select = ['*'])
    {
        try {
            $result = $this->model->select($select)->first();

            if ($result) {
                return [
                    'message' => __('services.data_fetched_successfully'),
                    'payload' => $result,
                    'status' => CoreConstants::STATUS_CODE_SUCCESS
                ];
            } else {
                return [
                    'message' => __('services.no_result_found'),
                    'payload' => null,
                    'status' => CoreConstants::STATUS_CODE_NOT_FOUND
                ];
            }
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return [
                'message' => __('services.something_went_wrong'),
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
            $data['name'] = LocaleContent::text($data['name'] ?? null);
            $data['address'] = LocaleContent::text($data['address'] ?? null);
            $data['description'] = LocaleContent::text($data['description'] ?? null);
            $data['taglines'] = LocaleContent::lists($data['taglines'] ?? null);

            $validate = Validator::make($data, [
                'name.ru' => 'required|string',
                'name.en' => 'nullable|string',
                'email' => 'required|email',
                'address.ru' => 'nullable|string',
                'address.en' => 'nullable|string',
                'description.ru' => 'nullable|string',
                'description.en' => 'nullable|string',
                'taglines.ru' => 'nullable|array',
                'taglines.en' => 'nullable|array',
            ]);

            if ($validate->fails()) {
                return [
                    'message' => __('services.bad_request'),
                    'payload' => $validate->errors(),
                    'status' => CoreConstants::STATUS_CODE_BAD_REQUEST
                ];
            }

            $newData['email'] = $data['email'];
            $newData['phone'] = isset($data['phone']) ? $data['phone'] : null;

            if (isset($data['seederCV'])) {
                $newData['cv'] = $data['seederCV'];
            }

            $translations = [
                'name' => $data['name'],
                'address' => $data['address'],
                'description' => $data['description'],
                'taglines' => $data['taglines'],
            ];

            $newData['social_links'] = $this->socialLinks($data['social_links'] ?? null);
            
            $existedRecord = $this->getAll();

            if ($existedRecord['status'] === CoreConstants::STATUS_CODE_SUCCESS) {
                $existedRecord = $existedRecord['payload'];
                LocaleContent::assign($existedRecord, $translations);
                $result = $existedRecord->update($newData);
            } else {
                $newData['avatar'] = '';
                $newData['cover'] = 'assets/common/img/cover/default.png';
                $result = $this->model->newInstance($newData);
                LocaleContent::assign($result, $translations);
                $result->save();
            }

            if ($result) {
                return [
                    'message' => __('services.data_updated_successfully'),
                    'payload' => $result,
                    'status' => CoreConstants::STATUS_CODE_SUCCESS
                ];
            } else {
                return [
                    'message' => __('services.something_went_wrong'),
                    'payload' => null,
                    'status'  => CoreConstants::STATUS_CODE_ERROR
                ];
            }
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return [
                'message' => __('services.something_went_wrong'),
                'payload' => $th->getMessage(),
                'status' => CoreConstants::STATUS_CODE_ERROR
            ];
        }
    }

    /**
     * Social links stay one list. Only the visible title is translated.
     *
     * @param mixed $links
     * @return string|null
     */
    private function socialLinks($links)
    {
        if ($links === null || $links === '') {
            return null;
        }

        if (is_string($links)) {
            $decoded = json_decode($links, true);
            $links = is_array($decoded) ? $decoded : [];
        }

        if (!is_array($links)) {
            return null;
        }

        $normalized = [];

        foreach ($links as $socialLink) {
            if (!is_array($socialLink)) {
                continue;
            }

            $title = LocaleContent::text($socialLink['title'] ?? '');

            if ($title['ru'] === '' || empty($socialLink['link']) || empty($socialLink['iconClass'])) {
                continue;
            }

            $socialLink['title'] = [
                'ru' => $title['ru'],
                'en' => $title['en'],
            ];
            $normalized[] = $socialLink;
        }

        return count($normalized) ? json_encode($normalized, JSON_UNESCAPED_UNICODE) : null;
    }

    /**
     * Process the update avatar request
     *
     * @param array $data
     * @return array
     */
    public function processUpdateAvatarRequest(array $data)
    {
        try {
            $validate = Validator::make($data, [
                'file' => 'required'
            ]);

            if ($validate->fails()) {
                return [
                    'message' => __('services.bad_request'),
                    'payload' => $validate->errors(),
                    'status' => CoreConstants::STATUS_CODE_BAD_REQUEST
                ];
            }

            $file = $data['file'];
            $extension = $file->extension() ? $file->extension() : 'png';
            $fileName = Str::random(10). '_'. time() .'.'. $extension;
            $pathName = 'assets/common/img/avatar/';
            
            if (!file_exists($pathName)) {
                mkdir($pathName, 0777, true);
            }

            if ($file->move($pathName, $fileName)) {
                //delete previous avatar
                $oldAvatarResponse = $this->getAll(['avatar', 'id']);
                try {
                    if ($oldAvatarResponse['status'] === CoreConstants::STATUS_CODE_SUCCESS && $oldAvatarResponse['payload']->hasCustomAvatar() && is_file($oldAvatarResponse['payload']->avatar)) {
                        unlink($oldAvatarResponse['payload']->avatar);
                        ImageOptimizationService::deleteVariants($oldAvatarResponse['payload']->avatar);
                    }
                } catch (\Throwable $th) {
                    Log::error($th->getMessage());
                }

                (new ImageOptimizationService())->optimizeAvatar($pathName.$fileName);

                $result = $oldAvatarResponse['payload'];

                $updateResponse = $result->update([
                    'avatar' => $pathName.$fileName
                ]);

                if ($updateResponse) {
                    return [
                        'message' => __('services.avatar_saved_successfully'),
                        'payload' => [
                            'file' => $pathName.$fileName
                        ],
                        'status' => CoreConstants::STATUS_CODE_SUCCESS
                    ];
                } else {
                    return [
                        'message' => __('services.something_went_wrong'),
                        'payload' => null,
                        'status' => CoreConstants::STATUS_CODE_ERROR
                    ];
                }
            } else {
                return [
                    'message' => __('services.file_could_not_be_saved'),
                    'payload' => null,
                    'status' => CoreConstants::STATUS_CODE_ERROR
                ];
            }
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return [
                'message' => __('services.something_went_wrong'),
                'payload' => $th->getMessage(),
                'status' => CoreConstants::STATUS_CODE_ERROR
            ];
        }
    }

    /**
     * Process the delete avatar request
     *
     * @param string $file
     * @return array
     */
    public function processDeleteAvatarRequest(string $file)
    {
        try {
            $result = $this->getAll();

            if ($result['status'] !== CoreConstants::STATUS_CODE_SUCCESS) {
                return $result;
            }

            $about = $result['payload'];
            $current = (string) $about->avatar;

            $matchesCurrent = $file === '' || $file === $current;
            $inAvatarDir = strpos($current, 'assets/common/img/avatar/') === 0 && strpos($current, '..') === false;

            if ($matchesCurrent && $about->hasCustomAvatar() && $inAvatarDir && is_file($current)) {
                unlink($current);
                ImageOptimizationService::deleteVariants($current);
            }

            if (!$about->update(['avatar' => ''])) {
                return [
                    'message' => __('services.something_went_wrong'),
                    'payload' => null,
                    'status' => CoreConstants::STATUS_CODE_ERROR
                ];
            }

            return [
                'message' => __('services.file_deleted_successfully'),
                'payload' => [
                    'file' => null
                ],
                'status' => CoreConstants::STATUS_CODE_SUCCESS
            ];
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return [
                'message' => __('services.something_went_wrong'),
                'payload' => $th->getMessage(),
                'status' => CoreConstants::STATUS_CODE_ERROR
            ];
        }
    }

    /**
     * Process the update cover request
     *
     * @param array $data
     * @return array
     */
    public function processUpdateCoverRequest(array $data)
    {
        try {
            $validate = Validator::make($data, [
                'file' => 'required'
            ]);

            if ($validate->fails()) {
                return [
                    'message' => __('services.bad_request'),
                    'payload' => $validate->errors(),
                    'status' => CoreConstants::STATUS_CODE_BAD_REQUEST
                ];
            }

            $file = $data['file'];
            $extension = $file->extension() ? $file->extension() : 'png';
            $fileName = Str::random(10). '_'. time() .'.'. $extension;
            $pathName = 'assets/common/img/cover/';
            
            if (!file_exists($pathName)) {
                mkdir($pathName, 0777, true);
            }

            if ($file->move($pathName, $fileName)) {
                //delete previous cover
                $oldCoverResponse = $this->getAll(['cover', 'id']);
                try {
                    if ($oldCoverResponse['status'] === CoreConstants::STATUS_CODE_SUCCESS && $oldCoverResponse['payload']->cover !== 'assets/common/img/cover/default.png' && file_exists($oldCoverResponse['payload']->cover)) {
                        unlink($oldCoverResponse['payload']->cover);
                    }
                } catch (\Throwable $th) {
                    Log::error($th->getMessage());
                }

                $result = $oldCoverResponse['payload'];

                $updateResponse = $result->update([
                    'cover' => $pathName.$fileName
                ]);

                if ($updateResponse) {
                    return [
                        'message' => __('services.cover_saved_successfully'),
                        'payload' => [
                            'file' => $pathName.$fileName
                        ],
                        'status' => CoreConstants::STATUS_CODE_SUCCESS
                    ];
                } else {
                    return [
                        'message' => __('services.something_went_wrong'),
                        'payload' => null,
                        'status' => CoreConstants::STATUS_CODE_ERROR
                    ];
                }
            } else {
                return [
                    'message' => __('services.file_could_not_be_saved'),
                    'payload' => null,
                    'status' => CoreConstants::STATUS_CODE_ERROR
                ];
            }
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return [
                'message' => __('services.something_went_wrong'),
                'payload' => $th->getMessage(),
                'status' => CoreConstants::STATUS_CODE_ERROR
            ];
        }
    }

    /**
     * Process the delete cover request
     *
     * @param string $file
     * @return array
     */
    public function processDeleteCoverRequest(string $file)
    {
        try {
            if (!file_exists($file)) {
                return [
                    'message' => __('services.file_not_found'),
                    'payload' => $file,
                    'status' => CoreConstants::STATUS_CODE_NOT_FOUND
                ];
            }

            if (unlink($file)) {
                $defaultCover = 'assets/common/img/cover/default.png';
                $result = $this->getAll();

                if ($result['status'] !== CoreConstants::STATUS_CODE_SUCCESS) {
                    return $result;
                } else {
                    $result = $result['payload'];
                }

                $updateResponse = $result->update([
                    'cover' => $defaultCover
                ]);

                if ($updateResponse) {
                    return [
                        'message' => __('services.file_deleted_successfully'),
                        'payload' => [
                            'file' => $defaultCover
                        ],
                        'status' => CoreConstants::STATUS_CODE_SUCCESS
                    ];
                } else {
                    return [
                        'message' => __('services.something_went_wrong'),
                        'payload' => null,
                        'status' => CoreConstants::STATUS_CODE_ERROR
                    ];
                }
            } else {
                return [
                    'message' => __('services.file_could_not_be_deleted'),
                    'payload' => null,
                    'status' => CoreConstants::STATUS_CODE_ERROR
                ];
            }
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return [
                'message' => __('services.something_went_wrong'),
                'payload' => $th->getMessage(),
                'status' => CoreConstants::STATUS_CODE_ERROR
            ];
        }
    }

    /**
     * Process the update CV request
     *
     * @param array $data
     * @return array
     */
    public function processUpdateCVRequest(array $data)
    {
        try {
            $validate = Validator::make($data, [
                'file' => 'required'
            ]);

            if ($validate->fails()) {
                return [
                    'message' => __('services.bad_request'),
                    'payload' => $validate->errors(),
                    'status' => CoreConstants::STATUS_CODE_BAD_REQUEST
                ];
            }
          
            $file = $data['file'];
            $extension = $file->extension() ? $file->extension() : 'pdf';
            $fileName = Str::random(10). '_'. time() .'.'. $extension;
            $pathName = 'assets/common/cv/';
            
            if (!file_exists($pathName)) {
                mkdir($pathName, 0777, true);
            }

            if ($file->move($pathName, $fileName)) {
                //delete previous cv
                $oldCVResponse = $this->getAll(['cv', 'id']);
                try {
                    if ($oldCVResponse['status'] === CoreConstants::STATUS_CODE_SUCCESS && $oldCVResponse['payload']->cv !== 'assets/common/cv/default.pdf' && file_exists($oldCVResponse['payload']->cv)) {
                        unlink($oldCVResponse['payload']->cv);
                    }
                } catch (\Throwable $th) {
                    Log::error($th->getMessage());
                }

                $result = $oldCVResponse['payload'];

                $updateResponse = $result->update([
                    'cv' => $pathName.$fileName
                ]);
                
                if ($updateResponse) {
                    return [
                        'message' => __('services.cv_saved_successfully'),
                        'payload' => [
                            'file' => $pathName.$fileName
                        ],
                        'status' => CoreConstants::STATUS_CODE_SUCCESS
                    ];
                } else {
                    return [
                        'message' => __('services.something_went_wrong'),
                        'payload' => null,
                        'status' => CoreConstants::STATUS_CODE_ERROR
                    ];
                }
            } else {
                return [
                    'message' => __('services.file_could_not_be_saved'),
                    'payload' => null,
                    'status' => CoreConstants::STATUS_CODE_ERROR
                ];
            }
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return [
                'message' => __('services.something_went_wrong'),
                'payload' => $th->getMessage(),
                'status' => CoreConstants::STATUS_CODE_ERROR
            ];
        }
    }

    /**
     * Process the delete CV request
     *
     * @param string $file
     * @return array
     */
    public function processDeleteCVRequest(string $file)
    {
        try {
            if (!file_exists($file)) {
                return [
                    'message' => __('services.file_not_found'),
                    'payload' => $file,
                    'status' => CoreConstants::STATUS_CODE_NOT_FOUND
                ];
            }

            if (unlink($file)) {
                $result = $this->getAll();
                if ($result['status'] !== CoreConstants::STATUS_CODE_SUCCESS) {
                    return $result;
                } else {
                    $result = $result['payload'];
                }

                $updateResponse = $result->update([
                    'cv' => null
                ]);

                if ($updateResponse) {
                    return [
                        'message' => __('services.file_deleted_successfully'),
                        'payload' => [
                            'file' => null
                        ],
                        'status' => CoreConstants::STATUS_CODE_SUCCESS
                    ];
                } else {
                    return [
                        'message'  => __('services.something_went_wrong'),
                        'payload' => null,
                        'status'  => CoreConstants::STATUS_CODE_ERROR
                    ];
                }
            } else {
                return [
                    'message'  => __('services.file_could_not_be_deleted'),
                    'payload' => null,
                    'status' => CoreConstants::STATUS_CODE_ERROR
                ];
            }
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return [
                'message' => __('services.something_went_wrong'),
                'payload' => $th->getMessage(),
                'status' => CoreConstants::STATUS_CODE_ERROR
            ];
        }
    }
}
