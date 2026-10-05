<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ConvertContentColumnsToTranslations extends Migration
{
    /**
     * String columns that must grow so {"ru","en"} JSON fits.
     * longText columns are wrapped in place.
     *
     * @var array
     */
    private $textColumns = [
        'projects' => [
            'title' => false,
            'details' => true,
        ],
        'services' => [
            'title' => false,
            'details' => false,
            'content' => true,
        ],
        'about' => [
            'name' => false,
            'address' => true,
            'description' => true,
        ],
        'skills' => [
            'name' => false,
        ],
        'experiences' => [
            'company' => false,
            'period' => true,
            'position' => false,
            'details' => true,
        ],
        'education' => [
            'institution' => false,
            'period' => true,
            'degree' => true,
            'department' => true,
            'thesis' => true,
        ],
    ];

    /**
     * JSON lists whose whole value becomes the Russian translation.
     *
     * @var array
     */
    private $listColumns = [
        'projects' => ['categories', 'buttons'],
        'about' => ['taglines'],
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach ($this->textColumns as $table => $columns) {
            foreach ($columns as $column => $nullable) {
                if ($this->needsText($table, $column)) {
                    $this->modifyText($table, $column, $nullable);
                }
            }
        }

        foreach ($this->textColumns as $table => $columns) {
            foreach (array_keys($columns) as $column) {
                $this->wrapColumn($table, $column, false);
            }
        }

        foreach ($this->listColumns as $table => $columns) {
            foreach ($columns as $column) {
                $this->wrapColumn($table, $column, true);
            }
        }

        $this->wrapSocialTitles();
        $this->wrapMetaTexts();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->unwrapSocialTitles();
        $this->unwrapMetaTexts();

        foreach ($this->listColumns as $table => $columns) {
            foreach ($columns as $column) {
                $this->unwrapColumn($table, $column, true);
            }
        }

        foreach ($this->textColumns as $table => $columns) {
            foreach (array_keys($columns) as $column) {
                $this->unwrapColumn($table, $column, false);
            }
        }

        foreach ($this->textColumns as $table => $columns) {
            foreach ($columns as $column => $nullable) {
                if ($this->wasString($table, $column)) {
                    $null = $nullable ? 'NULL' : 'NOT NULL';
                    DB::statement("ALTER TABLE `{$table}` MODIFY `{$column}` VARCHAR(255) {$null}");
                }
            }
        }
    }

    /**
     * @param string $table
     * @param string $column
     * @return bool
     */
    private function needsText(string $table, string $column): bool
    {
        $type = DB::selectOne('SHOW COLUMNS FROM `' . $table . '` WHERE Field = ?', [$column]);

        return $type && stripos($type->Type, 'varchar') !== false;
    }

    /**
     * Columns that this migration widened, so down() can shrink them again.
     *
     * @param string $table
     * @param string $column
     * @return bool
     */
    private function wasString(string $table, string $column): bool
    {
        $originals = [
            'projects' => ['title'],
            'services' => ['title'],
            'about' => ['name'],
            'skills' => ['name'],
            'experiences' => ['company', 'period', 'position'],
            'education' => ['institution', 'period', 'degree', 'department', 'thesis'],
        ];

        return in_array($column, $originals[$table] ?? [], true);
    }

    /**
     * @param string $table
     * @param string $column
     * @param bool $nullable
     * @return void
     */
    private function modifyText(string $table, string $column, bool $nullable): void
    {
        $null = $nullable ? 'NULL' : 'NOT NULL';
        DB::statement("ALTER TABLE `{$table}` MODIFY `{$column}` TEXT {$null}");
    }

    /**
     * @param string $table
     * @param string $column
     * @param bool $list
     * @return void
     */
    private function wrapColumn(string $table, string $column, bool $list): void
    {
        foreach (DB::table($table)->select('id', $column)->orderBy('id')->get() as $row) {
            $wrapped = $list ? $this->wrapList($row->{$column}) : $this->wrapText($row->{$column});

            if ($wrapped === $row->{$column}) {
                continue;
            }

            DB::table($table)->where('id', $row->id)->update([$column => $wrapped]);
        }
    }

    /**
     * @param string $table
     * @param string $column
     * @param bool $list
     * @return void
     */
    private function unwrapColumn(string $table, string $column, bool $list): void
    {
        foreach (DB::table($table)->select('id', $column)->orderBy('id')->get() as $row) {
            $plain = $list ? $this->unwrapList($row->{$column}) : $this->unwrapText($row->{$column});

            if ($plain === $row->{$column}) {
                continue;
            }

            DB::table($table)->where('id', $row->id)->update([$column => $plain]);
        }
    }

    /**
     * @param mixed $value
     * @return string|null
     */
    private function wrapText($value)
    {
        if ($value === null) {
            return null;
        }

        if ($this->alreadyTranslated($value)) {
            return $value;
        }

        return json_encode(['ru' => $value], JSON_UNESCAPED_UNICODE);
    }

    /**
     * @param mixed $value
     * @return string|null
     */
    private function wrapList($value)
    {
        if ($value === null || $value === '') {
            return $value;
        }

        if ($this->alreadyTranslated($value)) {
            return $value;
        }

        $decoded = json_decode($value, true);

        return json_encode([
            'ru' => is_array($decoded) ? $decoded : [],
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * @param mixed $value
     * @return string|null
     */
    private function unwrapText($value)
    {
        $pair = $this->pair($value);

        if ($pair === null) {
            return $value;
        }

        return $pair['ru'] === '' ? null : $pair['ru'];
    }

    /**
     * @param mixed $value
     * @return string|null
     */
    private function unwrapList($value)
    {
        $pair = $this->pair($value);

        if ($pair === null) {
            return $value;
        }

        $list = is_array($pair['ru']) ? $pair['ru'] : [];

        return json_encode($list, JSON_UNESCAPED_UNICODE);
    }

    /**
     * @param mixed $value
     * @return bool
     */
    private function alreadyTranslated($value): bool
    {
        return $this->pair($value) !== null;
    }

    /**
     * @param mixed $value
     * @return array|null
     */
    private function pair($value)
    {
        if (!is_string($value) || $value === '') {
            return null;
        }

        $decoded = json_decode($value, true);

        if (!is_array($decoded) || $this->isList($decoded)) {
            return null;
        }

        if (!array_key_exists('ru', $decoded) && !array_key_exists('en', $decoded)) {
            return null;
        }

        return $decoded;
    }

    /**
     * @return void
     */
    private function wrapSocialTitles(): void
    {
        foreach (DB::table('about')->select('id', 'social_links')->orderBy('id')->get() as $row) {
            $links = json_decode($row->social_links ?? '', true);

            if (!is_array($links)) {
                continue;
            }

            $changed = false;

            foreach ($links as &$link) {
                if (!is_array($link) || !isset($link['title']) || !is_string($link['title'])) {
                    continue;
                }

                $link['title'] = ['ru' => $link['title'], 'en' => ''];
                $changed = true;
            }

            unset($link);

            if ($changed) {
                DB::table('about')->where('id', $row->id)->update([
                    'social_links' => json_encode($links, JSON_UNESCAPED_UNICODE),
                ]);
            }
        }
    }

    /**
     * @return void
     */
    private function unwrapSocialTitles(): void
    {
        foreach (DB::table('about')->select('id', 'social_links')->orderBy('id')->get() as $row) {
            $links = json_decode($row->social_links ?? '', true);

            if (!is_array($links)) {
                continue;
            }

            $changed = false;

            foreach ($links as &$link) {
                if (!is_array($link) || !is_array($link['title'] ?? null)) {
                    continue;
                }

                $link['title'] = is_string($link['title']['ru'] ?? null) ? $link['title']['ru'] : '';
                $changed = true;
            }

            unset($link);

            if ($changed) {
                DB::table('about')->where('id', $row->id)->update([
                    'social_links' => json_encode($links, JSON_UNESCAPED_UNICODE),
                ]);
            }
        }
    }

    /**
     * portfolio_configs keys: meta title 15, author 16, description 17.
     *
     * @return void
     */
    private function wrapMetaTexts(): void
    {
        foreach (DB::table('portfolio_configs')->whereIn('setting_key', [15, 16, 17])->get() as $row) {
            $wrapped = $this->wrapText($row->setting_value);

            if ($wrapped === null || $wrapped === $row->setting_value) {
                continue;
            }

            DB::table('portfolio_configs')->where('id', $row->id)->update([
                'setting_value' => $wrapped,
            ]);
        }
    }

    /**
     * @return void
     */
    private function unwrapMetaTexts(): void
    {
        foreach (DB::table('portfolio_configs')->whereIn('setting_key', [15, 16, 17])->get() as $row) {
            $plain = $this->unwrapText($row->setting_value);

            DB::table('portfolio_configs')->where('id', $row->id)->update([
                'setting_value' => $plain ?? '',
            ]);
        }
    }

    /**
     * @param array $value
     * @return bool
     */
    private function isList(array $value): bool
    {
        if ($value === []) {
            return true;
        }

        return array_keys($value) === range(0, count($value) - 1);
    }
}
