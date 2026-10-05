<?php

namespace App\Services;

use CoreConstants;
use App\Helpers\ThemeRegistry;
use App\Models\PortfolioConfig;
use App\Services\Contracts\PortfolioConfigInterface;
use App\Support\LocaleContent;
use Illuminate\Validation\Rule;
use Log;
use Str;
use Validator;

class PortfolioConfigService implements PortfolioConfigInterface
{
    /**
     * Eloquent instance
     *
     * @var PortfolioConfig
     */
    private $model;

    /**
     * Create a new service instance.
     *
     * @return void
     */
    public function __construct(PortfolioConfig $portfolioConfig)
    {
        $this->model = $portfolioConfig;
    }

    /**
     * If config exist, update it. Otherwise insert new
     *
     * @param array $data
     * @return array
     */
    public function insertOrUpdate(array $data)
    {
        try {
            $validate = Validator::make($data, [
                'setting_key' => 'required',
            ]);

            if ($validate->fails()) {
                return [
                    'message' => 'Validation Error',
                    'payload' => $validate->errors(),
                    'status'  => CoreConstants::STATUS_CODE_BAD_REQUEST
                ];
            }

            if (isset($data['default_value'])) {
                $result = $this->model->updateOrCreate([
                    'setting_key' => $data['setting_key'],
                ], [
                    'setting_value' => isset($data['setting_value']) ? $data['setting_value'] : '',
                    'default_value' => isset($data['default_value']) ? $data['default_value'] : ''
                ]);
            } else {
                $result = $this->model->updateOrCreate([
                    'setting_key' => $data['setting_key'],
                ], [
                    'setting_value' => $data['setting_value']
                ]);
            }

            if ($result) {
                return [
                    'message' => 'Data is successfully updated',
                    'payload' => $result,
                    'status'  => CoreConstants::STATUS_CODE_SUCCESS
                ];
            } else {
                return [
                    'message' => 'Something went wrong',
                    'payload' => null,
                    'status'  => CoreConstants::STATUS_CODE_ERROR
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
     * Get single config by key
     *
     * @param int $key
     * @param array $select
     * @return array
     */
    public function getConfigByKey(int $key, array $select = ['*'])
    {
        try {
            $result = $this->model
                        ->select($select)
                        ->where('setting_key', $key)
                        ->first();
            if ($result) {
                return [
                    'message' => __('services.config_fetched_successfully'),
                    'payload' => $result,
                    'status' => CoreConstants::STATUS_CODE_SUCCESS
                ];
            } else {
                return [
                    'message' => __('services.config_not_found'),
                    'payload' => null,
                    'status'  => CoreConstants::STATUS_CODE_NOT_FOUND
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
     * Get all related Configs
     *
     * @param boolean $accentColor
     * @param boolean $googleAnalyticsId
     * @param boolean $maintenanceMode
     * @param boolean $template
     * @param boolean $seo
     * @param boolean $visibility
     * @param boolean $script
     * @return array
     */
    public function getAllConfigData(
        bool $accentColor = true,
        bool $googleAnalyticsId = true,
        bool $maintenanceMode = true,
        bool $template = true,
        bool $seo = true,
        bool $visibility = true,
        bool $script = true
    ) {
        try {
            $data = [];

            if ($template) {
                $result = $this->getConfigByKey(CoreConstants::PORTFOLIO_CONFIG__TEMPLATE, ['setting_value']);
                $data['template'] = ThemeRegistry::resolve(
                    $result['status'] === CoreConstants::STATUS_CODE_SUCCESS ? $result['payload']->setting_value : null
                );
            }

            if ($accentColor) {
                $result = $this->getConfigByKey(CoreConstants::PORTFOLIO_CONFIG__ACCENT_COLOR, ['setting_value']);
                if ($result['status'] === CoreConstants::STATUS_CODE_SUCCESS) {
                    $data['accentColor'] = $result['payload']->setting_value;
                } else {
                    $data['accentColor'] = '#1890ff';
                }
            }

            if ($googleAnalyticsId) {
                $result = $this->getConfigByKey(CoreConstants::PORTFOLIO_CONFIG__GOOGLE_ANALYTICS_ID, ['setting_value']);
                if ($result['status'] === CoreConstants::STATUS_CODE_SUCCESS) {
                    $data['googleAnalyticsId'] = $result['payload']->setting_value;
                } else {
                    $data['googleAnalyticsId'] = '';
                }
            }

            if ($maintenanceMode) {
                $result = $this->getConfigByKey(CoreConstants::PORTFOLIO_CONFIG__MAINTENANCE_MODE, ['setting_value']);
                if ($result['status'] === CoreConstants::STATUS_CODE_SUCCESS) {
                    $data['maintenanceMode'] = $result['payload']->setting_value;
                } else {
                    $data['maintenanceMode'] = CoreConstants::FALSE;
                }
            }

            if ($visibility) {
                $result = $this->getConfigByKey(CoreConstants::PORTFOLIO_CONFIG__VISIBILITY_ABOUT, ['setting_value']);
                if ($result['status'] === CoreConstants::STATUS_CODE_SUCCESS) {
                    $data['visibility']['about'] = $result['payload']->setting_value;
                } else {
                    $data['visibility']['about'] = CoreConstants::TRUE;
                }

                $result = $this->getConfigByKey(CoreConstants::PORTFOLIO_CONFIG__VISIBILITY_SKILL, ['setting_value']);
                if ($result['status'] === CoreConstants::STATUS_CODE_SUCCESS) {
                    $data['visibility']['skills'] = $result['payload']->setting_value;
                } else {
                    $data['visibility']['skills'] = CoreConstants::TRUE;
                }

                $result = $this->getConfigByKey(CoreConstants::PORTFOLIO_CONFIG__VISIBILITY_EDUCATION, ['setting_value']);
                if ($result['status'] === CoreConstants::STATUS_CODE_SUCCESS) {
                    $data['visibility']['education'] = $result['payload']->setting_value;
                } else {
                    $data['visibility']['education'] = CoreConstants::TRUE;
                }

                $result = $this->getConfigByKey(CoreConstants::PORTFOLIO_CONFIG__VISIBILITY_EXPERIENCE, ['setting_value']);
                if ($result['status'] === CoreConstants::STATUS_CODE_SUCCESS) {
                    $data['visibility']['experiences'] = $result['payload']->setting_value;
                } else {
                    $data['visibility']['experiences'] = CoreConstants::TRUE;
                }

                $result = $this->getConfigByKey(CoreConstants::PORTFOLIO_CONFIG__VISIBILITY_PROJECT, ['setting_value']);
                if ($result['status'] === CoreConstants::STATUS_CODE_SUCCESS) {
                    $data['visibility']['projects'] = $result['payload']->setting_value;
                } else {
                    $data['visibility']['projects'] = CoreConstants::TRUE;
                }

                $result = $this->getConfigByKey(CoreConstants::PORTFOLIO_CONFIG__VISIBILITY_SERVICE, ['setting_value']);
                if ($result['status'] === CoreConstants::STATUS_CODE_SUCCESS) {
                    $data['visibility']['services'] = $result['payload']->setting_value;
                } else {
                    $data['visibility']['services'] = CoreConstants::TRUE;
                }

                $result = $this->getConfigByKey(CoreConstants::PORTFOLIO_CONFIG__VISIBILITY_CONTACT, ['setting_value']);
                if ($result['status'] === CoreConstants::STATUS_CODE_SUCCESS) {
                    $data['visibility']['contact'] = $result['payload']->setting_value;
                } else {
                    $data['visibility']['contact'] = CoreConstants::TRUE;
                }

                $result = $this->getConfigByKey(CoreConstants::PORTFOLIO_CONFIG__VISIBILITY_FOOTER, ['setting_value']);
                if ($result['status'] === CoreConstants::STATUS_CODE_SUCCESS) {
                    $data['visibility']['footer'] = $result['payload']->setting_value;
                } else {
                    $data['visibility']['footer'] = CoreConstants::TRUE;
                }

                $result = $this->getConfigByKey(CoreConstants::PORTFOLIO_CONFIG__VISIBILITY_CV, ['setting_value']);
                if ($result['status'] === CoreConstants::STATUS_CODE_SUCCESS) {
                    $data['visibility']['cv'] = $result['payload']->setting_value;
                } else {
                    $data['visibility']['cv'] = CoreConstants::TRUE;
                }

                $result = $this->getConfigByKey(CoreConstants::PORTFOLIO_CONFIG__VISIBILITY_SKILL_PROFICIENCY, ['setting_value']);
                if ($result['status'] === CoreConstants::STATUS_CODE_SUCCESS) {
                    $data['visibility']['skillProficiency'] = $result['payload']->setting_value;
                } else {
                    $data['visibility']['skillProficiency'] = CoreConstants::TRUE;
                }
            }

            if ($script) {
                $result = $this->getConfigByKey(CoreConstants::PORTFOLIO_CONFIG__SCRIPT_HEADER, ['setting_value']);
                if ($result['status'] === CoreConstants::STATUS_CODE_SUCCESS) {
                    $data['script']['header'] = $result['payload']->setting_value;
                } else {
                    $data['script']['header'] = '';
                }

                $result = $this->getConfigByKey(CoreConstants::PORTFOLIO_CONFIG__SCRIPT_FOOTER, ['setting_value']);
                if ($result['status'] === CoreConstants::STATUS_CODE_SUCCESS) {
                    $data['script']['footer'] = $result['payload']->setting_value;
                } else {
                    $data['script']['footer'] = '';
                }
            }

            if ($seo) {
                $data['seo']['translations'] = [];

                foreach ([
                    'title' => CoreConstants::PORTFOLIO_CONFIG__META_TITLE,
                    'author' => CoreConstants::PORTFOLIO_CONFIG__META_AUTHOR,
                    'description' => CoreConstants::PORTFOLIO_CONFIG__META_DESCRIPTION,
                ] as $field => $key) {
                    $result = $this->getConfigByKey($key, ['setting_value']);
                    $pair = $result['status'] === CoreConstants::STATUS_CODE_SUCCESS
                        ? LocaleContent::decodeStoredText($result['payload']->setting_value)
                        : ['ru' => '', 'en' => ''];
                    $data['seo'][$field] = LocaleContent::pick($pair);
                    $data['seo']['translations'][$field] = $pair;
                }

                $result = $this->getConfigByKey(CoreConstants::PORTFOLIO_CONFIG__META_IMAGE, ['setting_value']);
                if ($result['status'] === CoreConstants::STATUS_CODE_SUCCESS) {
                    $data['seo']['image'] = $result['payload']->setting_value;
                } else {
                    $data['seo']['image'] = '';
                }
            }

            return [
                'message' => __('services.configs_fetched_successfully'),
                'payload' => $data,
                'status' => CoreConstants::STATUS_CODE_SUCCESS
            ];
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
     * Set single Config
     *
     * @param array $data
     * @return array
     */
    public function setConfigData(array $data)
    {
        try {
            $rules = [
                'setting_key' => 'required',
            ];

            if (isset($data['setting_key']) && (int) $data['setting_key'] === CoreConstants::PORTFOLIO_CONFIG__TEMPLATE) {
                $rules['setting_value'] = ['required', Rule::in(ThemeRegistry::ids())];
            }

            $validate = Validator::make($data, $rules);

            if ($validate->fails()) {
                return [
                    'message' => 'Validation Error',
                    'payload' => $validate->errors(),
                    'status' => CoreConstants::STATUS_CODE_BAD_REQUEST
                ];
            }

            $newData['setting_key']   = $data['setting_key'];
            $newData['setting_value'] = isset($data['setting_value']) ? $data['setting_value'] : '';

            $result = $this->insertOrUpdate($newData);
            
            if ($result['status'] === CoreConstants::STATUS_CODE_SUCCESS) {
                return [
                    'message' => __('services.config_updated_successfully'),
                    'payload' => $result['payload'],
                    'status' => CoreConstants::STATUS_CODE_SUCCESS
                ];
            } else {
                return $result;
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
     * Keep an existing English value when a legacy client sends one plain string.
     *
     * @param int $settingKey
     * @param mixed $incoming
     * @return array{ru: string, en: string}
     */
    private function metaPair(int $settingKey, $incoming): array
    {
        $current = $this->getConfigByKey($settingKey, ['setting_value']);
        $existing = $current['status'] === CoreConstants::STATUS_CODE_SUCCESS
            ? LocaleContent::decodeStoredText($current['payload']->setting_value)
            : ['ru' => '', 'en' => ''];
        $pair = LocaleContent::text($incoming ?? '');

        if (is_string($incoming) || is_numeric($incoming)) {
            $pair['en'] = $existing['en'];
        }

        return $pair;
    }

    /**
     * Store meta data
     *
     * @param array $data
     * @return array
     */
    public function setMetaData(array $data)
    {
        try {
            $count = 0;
            $inserted = [];

            $inserted['translations'] = [];

            foreach ($data as $key => $value) {
                if (in_array($key, ['title', 'author', 'description'], true)) {
                    $settingKey = [
                        'title' => CoreConstants::PORTFOLIO_CONFIG__META_TITLE,
                        'author' => CoreConstants::PORTFOLIO_CONFIG__META_AUTHOR,
                        'description' => CoreConstants::PORTFOLIO_CONFIG__META_DESCRIPTION,
                    ][$key];
                    $pair = $this->metaPair($settingKey, $value);
                    $newData = [
                        'setting_key' => $settingKey,
                        'setting_value' => json_encode($pair, JSON_UNESCAPED_UNICODE),
                    ];
                    $result = $this->insertOrUpdate($newData);

                    if ($result['status'] === CoreConstants::STATUS_CODE_SUCCESS) {
                        $count++;
                        $inserted[$key] = LocaleContent::pick($pair);
                        $inserted['translations'][$key] = $pair;
                    } else {
                        Log::error($result['payload']);
                    }
                } elseif ($key === 'image') {
                    $file = $data['image'];
                    if ($file) {
                        $extension = $file->extension() ? $file->extension() : 'png';
                        $fileName = Str::random(10). '_'. time() .'.'. $extension;
                        $pathName = 'assets/common/img/meta-image/';
                        
                        if (!file_exists($pathName)) {
                            mkdir($pathName, 0777, true);
                        }

                        if ($file->move($pathName, $fileName)) {
                            //delete previous image
                            try {
                                $oldImageResponse = $this->getConfigByKey(CoreConstants::PORTFOLIO_CONFIG__META_IMAGE);
                                if ($oldImageResponse['status'] === CoreConstants::STATUS_CODE_SUCCESS && file_exists($oldImageResponse['payload']->setting_value)) {
                                    unlink($oldImageResponse['payload']->setting_value);
                                }
                            } catch (\Throwable $th) {
                                Log::error($th->getMessage());
                            }

                            $newData = [
                                'setting_key' => CoreConstants::PORTFOLIO_CONFIG__META_IMAGE,
                                'setting_value' => $pathName.$fileName,
                            ];
                            $result = $this->insertOrUpdate($newData);

                            if ($result['status'] === CoreConstants::STATUS_CODE_SUCCESS) {
                                $count++;
                                $inserted['image'] = $result['payload']->setting_value;
                            } else {
                                Log::error($result['payload']);
                            }
                        }
                    } else {
                        //delete previous image
                        try {
                            $oldImageResponse = $this->getConfigByKey(CoreConstants::PORTFOLIO_CONFIG__META_IMAGE);
                            if ($oldImageResponse['status'] === CoreConstants::STATUS_CODE_SUCCESS && file_exists($oldImageResponse['payload']->setting_value)) {
                                unlink($oldImageResponse['payload']->setting_value);
                            }
                        } catch (\Throwable $th) {
                            Log::error($th->getMessage());
                        }

                        $newData = [
                            'setting_key' => CoreConstants::PORTFOLIO_CONFIG__META_IMAGE,
                            'setting_value' => '',
                        ];
                        $result = $this->insertOrUpdate($newData);
                    }
                }
            }
            if ($count) {
                return [
                    'message' => __('services.seo_info_saved_successfully'),
                    'payload' => [
                        'count' => $count,
                        'inserted' => $inserted
                    ],
                    'status' => CoreConstants::STATUS_CODE_SUCCESS
                ];
            } else {
                return [
                    'message' => __('services.nothing_is_updated'),
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
}
