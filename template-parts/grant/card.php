<?php
/**
 * Template Part: Grant Card - Enhanced UX Version
 * 補助金カード（UX最適化版）
 * 
 * @package Grant_Insight_Perfect
 * @subpackage Grant_System
 * @version 3.0.0 - Enhanced UX with Clear Hierarchy
 */

// セキュリティチェック
if (!defined('ABSPATH')) {
    exit;
}

// 現在の投稿情報を取得
$post_id = get_the_ID();

// ACFフィールドを使用（single-grant.phpと統一）
$deadline_date = function_exists('get_field') ? get_field('deadline_date', $post_id) : '';
$max_amount = function_exists('get_field') ? get_field('max_amount', $post_id) : '';
$max_amount_numeric = function_exists('get_field') ? intval(get_field('max_amount_numeric', $post_id)) : 0;
$application_status = function_exists('get_field') ? get_field('application_status', $post_id) : 'open';
$grant_difficulty = function_exists('get_field') ? get_field('grant_difficulty', $post_id) : 'normal';
$views_count = function_exists('get_field') ? intval(get_field('views_count', $post_id)) : 0;
$is_featured = function_exists('get_field') ? get_field('is_featured', $post_id) : false;

// タクソノミー取得
$categories = get_the_terms($post_id, 'grant_category');
$prefectures = get_the_terms($post_id, 'grant_prefecture');
$municipalities = get_the_terms($post_id, 'grant_municipality');

// 締切日までの日数計算
$days_left = null;
$is_urgent = false;
$deadline_class = '';
if (!empty($deadline_date)) {
    $deadline_timestamp = strtotime($deadline_date);
    if ($deadline_timestamp && $deadline_timestamp > 0) {
        $current_time = current_time('timestamp');
        $days_left = ceil(($deadline_timestamp - $current_time) / 86400);
        
        if ($days_left <= 0) {
            $deadline_class = 'expired';
        } elseif ($days_left <= 7) {
            $deadline_class = 'urgent';
            $is_urgent = true;
        } elseif ($days_left <= 30) {
            $deadline_class = 'warning';
        }
    }
}

// 新着判定（7日以内）
$is_new = (strtotime(get_the_date('Y-m-d')) > strtotime('-7 days'));

// 金額フォーマット
$formatted_amount = '';
if ($max_amount_numeric > 0) {
    if ($max_amount_numeric >= 100000000) {
        $formatted_amount = number_format($max_amount_numeric / 100000000, 1) . '億円';
    } elseif ($max_amount_numeric >= 10000) {
        $formatted_amount = number_format($max_amount_numeric / 10000) . '万円';
    } else {
        $formatted_amount = number_format($max_amount_numeric) . '円';
    }
} elseif (!empty($max_amount)) {
    $formatted_amount = $max_amount;
}

// 地域表示
$location_display = '';
if (!empty($municipalities) && !is_wp_error($municipalities)) {
    $location_display = $municipalities[0]->name;
    if (count($municipalities) > 1) {
        $location_display .= ' 他' . (count($municipalities) - 1) . '市町村';
    }
} elseif (!empty($prefectures) && !is_wp_error($prefectures)) {
    // 全国チェック
    if (count($prefectures) >= 47 || in_array('zenkoku', wp_list_pluck($prefectures, 'slug'))) {
        $location_display = '全国';
    } else {
        $location_display = $prefectures[0]->name;
        if (count($prefectures) > 1) {
            $location_display .= ' 他' . (count($prefectures) - 1) . '都道府県';
        }
    }
}

// AI要約を取得
$ai_summary = function_exists('get_field') ? get_field('ai_summary', $post_id) : '';
$excerpt = '';
if (!empty($ai_summary)) {
    $excerpt = wp_trim_words(strip_tags($ai_summary), 25, '...');
} elseif (has_excerpt()) {
    $excerpt = wp_trim_words(get_the_excerpt(), 25, '...');
} else {
    $excerpt = wp_trim_words(strip_tags(get_the_content()), 25, '...');
}

// 難易度表示
$difficulty_labels = array(
    'easy' => array('label' => '易', 'color' => '#4caf50'),
    'normal' => array('label' => '中', 'color' => '#ff9800'),
    'hard' => array('label' => '難', 'color' => '#f44336'),
);
$difficulty_data = isset($difficulty_labels[$grant_difficulty]) ? $difficulty_labels[$grant_difficulty] : $difficulty_labels['normal'];
?>

<article class="grant-card-enhanced" itemscope itemtype="https://schema.org/GovernmentService">
    <a href="<?php the_permalink(); ?>" class="grant-card-link" itemprop="url">
        
        <!-- ステータスインジケーター（左端の色付きバー） -->
        <?php if ($is_urgent) : ?>
        <div class="grant-status-indicator status-urgent" title="締切間近"></div>
        <?php elseif ($is_new) : ?>
        <div class="grant-status-indicator status-new" title="新着"></div>
        <?php elseif ($is_featured) : ?>
        <div class="grant-status-indicator status-featured" title="注目"></div>
        <?php else : ?>
        <div class="grant-status-indicator status-normal"></div>
        <?php endif; ?>
        
        <!-- メインコンテンツ -->
        <div class="grant-card-content">
            
            <!-- ヘッダー: バッジと重要情報 -->
            <div class="grant-card-header">
                
                <!-- バッジグループ -->
                <div class="grant-badges">
                    <?php if ($is_urgent && $days_left !== null) : ?>
                    <span class="badge badge-urgent">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                        </svg>
                        残り<?php echo $days_left; ?>日
                    </span>
                    <?php endif; ?>
                    
                    <?php if ($is_featured) : ?>
                    <span class="badge badge-featured">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor">
                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                        </svg>
                        注目
                    </span>
                    <?php endif; ?>
                    
                    <?php if ($is_new) : ?>
                    <span class="badge badge-new">NEW</span>
                    <?php endif; ?>
                    
                    <?php if ($application_status === 'open') : ?>
                    <span class="badge badge-open">募集中</span>
                    <?php endif; ?>
                </div>
                
                <!-- 難易度インジケーター -->
                <div class="grant-difficulty" title="申請難易度: <?php echo esc_attr($difficulty_data['label']); ?>">
                    <span class="difficulty-label">難易度</span>
                    <span class="difficulty-badge" style="background: <?php echo esc_attr($difficulty_data['color']); ?>">
                        <?php echo esc_html($difficulty_data['label']); ?>
                    </span>
                </div>
                
            </div>
            
            <!-- タイトル -->
            <h3 class="grant-title" itemprop="name">
                <?php the_title(); ?>
            </h3>
            
            <!-- 重要メタ情報（金額・締切・地域） -->
            <div class="grant-primary-meta">
                <?php if ($formatted_amount) : ?>
                <div class="meta-primary meta-amount">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <line x1="12" y1="1" x2="12" y2="23"/>
                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                    </svg>
                    <span class="meta-label">最大</span>
                    <span class="meta-value"><?php echo esc_html($formatted_amount); ?></span>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($deadline_date) && $days_left !== null) : ?>
                <div class="meta-primary meta-deadline <?php echo esc_attr($deadline_class); ?>">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="12 6 12 12 16 14"/>
                    </svg>
                    <span class="meta-label">締切</span>
                    <span class="meta-value">
                        <?php echo date('Y/m/d', strtotime($deadline_date)); ?>
                        <?php if ($days_left > 0) : ?>
                        <span class="days-left">(<?php echo $days_left; ?>日)</span>
                        <?php endif; ?>
                    </span>
                </div>
                <?php endif; ?>
                
                <?php if ($location_display) : ?>
                <div class="meta-primary meta-location">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                        <circle cx="12" cy="10" r="3"/>
                    </svg>
                    <span class="meta-value"><?php echo esc_html($location_display); ?></span>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- カテゴリタグ -->
            <?php if ($categories && !is_wp_error($categories)) : ?>
            <div class="grant-category-tags">
                <?php foreach (array_slice($categories, 0, 3) as $category) : ?>
                <span class="category-tag"><?php echo esc_html($category->name); ?></span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <!-- 抜粋 -->
            <p class="grant-excerpt" itemprop="description">
                <?php echo esc_html($excerpt); ?>
            </p>
            
            <!-- フッター: 補足情報 -->
            <div class="grant-footer">
                <div class="grant-secondary-meta">
                    <?php if ($views_count > 0) : ?>
                    <span class="meta-secondary meta-views">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                        <?php echo number_format($views_count); ?>回
                    </span>
                    <?php endif; ?>
                    
                    <time class="meta-secondary meta-date" datetime="<?php echo get_the_date('c'); ?>">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                            <line x1="16" y1="2" x2="16" y2="6"/>
                            <line x1="8" y1="2" x2="8" y2="6"/>
                            <line x1="3" y1="10" x2="21" y2="10"/>
                        </svg>
                        <?php echo get_the_date('Y/m/d'); ?>
                    </time>
                </div>
                
                <div class="grant-action-hint">
                    <span>詳細を見る</span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <polyline points="9 18 15 12 9 6"/>
                    </svg>
                </div>
            </div>
            
        </div>
        
    </a>
</article>

<style>
/* ========================================
   Enhanced Grant Card - User First UX Design
   補助金カード（UX最適化版）
   ======================================== */

.grant-card-enhanced {
    background: #ffffff;
    border-bottom: 2px solid #e5e5e5;
    position: relative;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.grant-card-enhanced:hover {
    background-color: #fafafa;
    border-bottom-color: #000000;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.grant-card-enhanced:last-child {
    border-bottom: none;
}

/* ステータスインジケーター（左端の色付きバー） */
.grant-status-indicator {
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 4px;
    transition: width 0.3s ease;
}

.grant-card-enhanced:hover .grant-status-indicator {
    width: 6px;
}

.status-urgent {
    background: linear-gradient(180deg, #ff1744 0%, #f50057 100%);
}

.status-new {
    background: linear-gradient(180deg, #00e676 0%, #00c853 100%);
}

.status-featured {
    background: linear-gradient(180deg, #ffd600 0%, #ffab00 100%);
}

.status-normal {
    background: #e5e5e5;
}

/* カードリンク */
.grant-card-link {
    display: flex;
    padding: 20px 24px 20px 28px;
    text-decoration: none;
    color: inherit;
    align-items: stretch;
}

/* メインコンテンツ */
.grant-card-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

/* ヘッダー */
.grant-card-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
}

/* バッジグループ */
.grant-badges {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
}

.grant-card-enhanced .badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 5px 10px;
    border-radius: 0;
    font-size: 11px;
    font-weight: 700;
    color: #ffffff;
    line-height: 1;
    letter-spacing: 0.3px;
}

.badge-urgent {
    background: #ff1744;
    animation: pulse-urgent 2s infinite;
}

@keyframes pulse-urgent {
    0%, 100% { 
        opacity: 1;
        transform: scale(1);
    }
    50% { 
        opacity: 0.9;
        transform: scale(1.02);
    }
}

.badge-featured {
    background: #ff6f00;
}

.badge-new {
    background: #00c853;
}

.badge-open {
    background: #0091ea;
}

/* 難易度インジケーター */
.grant-difficulty {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 11px;
    white-space: nowrap;
}

.difficulty-label {
    color: #999999;
    font-weight: 600;
}

.difficulty-badge {
    padding: 4px 8px;
    border-radius: 0;
    color: #ffffff;
    font-weight: 700;
    font-size: 11px;
}

/* タイトル */
.grant-title {
    font-size: 17px;
    font-weight: 800;
    line-height: 1.5;
    color: #000000;
    margin: 0;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    transition: color 0.2s ease;
    letter-spacing: -0.01em;
}

.grant-card-enhanced:hover .grant-title {
    color: #0066cc;
}

/* 重要メタ情報（金額・締切・地域） */
.grant-primary-meta {
    display: flex;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
}

.meta-primary {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 14px;
    font-weight: 700;
    padding: 8px 12px;
    background: #f5f5f5;
    border-radius: 0;
    border-left: 3px solid;
}

.meta-amount {
    border-left-color: #2196f3;
    color: #1976d2;
}

.meta-amount svg {
    stroke: #2196f3;
}

.meta-deadline {
    border-left-color: #ff9800;
    color: #f57c00;
}

.meta-deadline.urgent {
    background: #ffebee;
    border-left-color: #f44336;
    color: #c62828;
}

.meta-deadline.warning {
    background: #fff3e0;
}

.meta-deadline svg {
    stroke: currentColor;
}

.meta-location {
    border-left-color: #4caf50;
    color: #388e3c;
}

.meta-location svg {
    stroke: #4caf50;
}

.meta-label {
    font-size: 12px;
    font-weight: 600;
    opacity: 0.8;
}

.meta-value {
    font-size: 15px;
    font-weight: 800;
}

.days-left {
    font-size: 12px;
    font-weight: 600;
    margin-left: 4px;
    opacity: 0.9;
}

/* カテゴリタグ */
.grant-category-tags {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.category-tag {
    padding: 5px 12px;
    background: #000000;
    color: #ffffff;
    font-size: 12px;
    font-weight: 600;
    border-radius: 0;
    letter-spacing: 0.3px;
}

/* 抜粋 */
.grant-excerpt {
    font-size: 14px;
    line-height: 1.7;
    color: #666666;
    margin: 0;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* フッター */
.grant-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    margin-top: auto;
    padding-top: 8px;
    border-top: 1px solid #f0f0f0;
}

.grant-secondary-meta {
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
}

.meta-secondary {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 12px;
    color: #999999;
    font-weight: 500;
}

.meta-secondary svg {
    stroke: currentColor;
    opacity: 0.7;
}

/* アクションヒント */
.grant-action-hint {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 13px;
    font-weight: 600;
    color: #0066cc;
    opacity: 0;
    transform: translateX(-10px);
    transition: all 0.3s ease;
}

.grant-card-enhanced:hover .grant-action-hint {
    opacity: 1;
    transform: translateX(0);
}

.grant-action-hint svg {
    stroke: currentColor;
}

/* レスポンシブ - タブレット */
@media (max-width: 768px) {
    .grant-card-link {
        padding: 18px 20px 18px 24px;
    }
    
    .grant-card-header {
        flex-direction: column;
        gap: 10px;
    }
    
    .grant-title {
        font-size: 16px;
    }
    
    .grant-primary-meta {
        gap: 12px;
    }
    
    .meta-primary {
        padding: 6px 10px;
        font-size: 13px;
    }
    
    .meta-value {
        font-size: 14px;
    }
}

/* レスポンシブ - モバイル */
@media (max-width: 640px) {
    .grant-card-link {
        padding: 16px 16px 16px 20px;
    }
    
    .grant-status-indicator {
        width: 3px;
    }
    
    .grant-card-enhanced:hover .grant-status-indicator {
        width: 4px;
    }
    
    .grant-card-content {
        gap: 10px;
    }
    
    .grant-title {
        font-size: 15px;
        -webkit-line-clamp: 3;
    }
    
    .grant-primary-meta {
        flex-direction: column;
        align-items: stretch;
        gap: 8px;
    }
    
    .meta-primary {
        width: 100%;
        justify-content: flex-start;
    }
    
    .grant-excerpt {
        font-size: 13px;
        line-height: 1.6;
    }
    
    .grant-footer {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    
    .grant-action-hint {
        opacity: 1;
        transform: translateX(0);
        font-size: 12px;
    }
}

/* レスポンシブ - 小型モバイル */
@media (max-width: 480px) {
    .grant-card-link {
        padding: 14px 12px 14px 16px;
    }
    
    .grant-badges {
        gap: 4px;
    }
    
    .grant-card-enhanced .badge {
        padding: 4px 8px;
        font-size: 10px;
    }
    
    .grant-title {
        font-size: 14px;
    }
    
    .meta-primary {
        padding: 5px 8px;
        font-size: 12px;
    }
    
    .meta-value {
        font-size: 13px;
    }
    
    .category-tag {
        padding: 4px 8px;
        font-size: 11px;
    }
    
    .grant-excerpt {
        font-size: 12px;
        -webkit-line-clamp: 3;
    }
}

/* プリント対応 */
@media print {
    .grant-card-enhanced {
        page-break-inside: avoid;
        border-bottom: 1px solid #cccccc;
        box-shadow: none !important;
    }
    
    .grant-status-indicator {
        display: none;
    }
    
    .grant-badges {
        display: none;
    }
    
    .grant-action-hint {
        display: none;
    }
    
    .grant-card-enhanced:hover {
        background-color: transparent;
    }
}

/* アクセシビリティ */
.grant-card-link:focus-visible {
    outline: 3px solid #2196f3;
    outline-offset: 2px;
}

/* アニメーション無効設定 */
@media (prefers-reduced-motion: reduce) {
    .grant-card-enhanced,
    .grant-status-indicator,
    .grant-title,
    .badge-urgent,
    .grant-action-hint {
        transition: none !important;
        animation: none !important;
    }
}

/* ダークモード対応 */
@media (prefers-color-scheme: dark) {
    .grant-card-enhanced {
        background: #1a1a1a;
        border-bottom-color: #333333;
    }
    
    .grant-card-enhanced:hover {
        background-color: #222222;
        border-bottom-color: #ffffff;
    }
    
    .grant-title {
        color: #ffffff;
    }
    
    .grant-excerpt {
        color: #cccccc;
    }
    
    .meta-primary {
        background: #2a2a2a;
    }
    
    .category-tag {
        background: #ffffff;
        color: #000000;
    }
    
    .grant-footer {
        border-top-color: #333333;
    }
}

</style>
