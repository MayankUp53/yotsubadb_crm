<?php
declare(strict_types=1);

namespace App\Service;

/** The five active master sections in app/View/Settings/index.ctp. */
final class LegacySettings
{
    public static function sections(): array
    {
        $sections = [
            'MtProducts' => ['title' => '商品', 'table' => 'mt_products', 'model' => 'MtProduct', 'slug' => 'mt-products'],
            'MtDispositions' => ['title' => '結果', 'table' => 'mt_dispositions', 'model' => 'MtDisposition', 'slug' => 'mt-dispositions'],
            'MtCategories' => ['title' => '症状', 'table' => 'mt_categories', 'model' => 'MtCategory', 'slug' => 'mt-categories'],
            'MtSubcategories' => ['title' => '状況', 'table' => 'mt_subcategories', 'model' => 'MtSubcategory', 'slug' => 'mt-subcategories'],
            'MtStatuses' => ['title' => '購入動機', 'table' => 'mt_statuses', 'model' => 'MtStatus', 'slug' => 'mt-statuses'],
        ];
        foreach ($sections as $name => &$section) {
            $formTitle = in_array($name, ['MtDispositions', 'MtCategories', 'MtSubcategories'], true)
                ? $section['title'] . '区分' : $section['title'];
            $section['list_title'] = $name === 'MtCategories' ? '症状区分一覧' : $section['title'] . '一覧';
            $section['add_title'] = $formTitle . ($name === 'MtDispositions' ? '登録' : '新規登録');
            $section['edit_title'] = ($name === 'MtProducts' ? '商品名' : $formTitle) . '更新';
            $section['detail_title'] = $formTitle . '詳細';
        }
        unset($section);
        return $sections;
    }
}
