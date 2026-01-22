<?php
/**
 * Plugin Name: AI Share Buttons
 * Plugin URI: https://kahunam.com/plugins/ai-share-buttons
 * Description: Share buttons for AI platforms and social networks. CSS-only dropdowns, no JavaScript.
 * Version: 2.0.0
 * Author: Kahunam
 * Author URI: https://kahunam.com
 * License: GPL v2 or later
 * Text Domain: kaais
 * Requires at least: 5.0
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
    exit;
}

define('KAAIS_VERSION', '2.0.0');
define('KAAIS_PATH', plugin_dir_path(__FILE__));
define('KAAIS_URL', plugin_dir_url(__FILE__));

/**
 * Get default settings
 */
function kaais_get_defaults() {
    return [
        'ai_platforms' => [
            'chatgpt' => true,
            'claude' => true,
            'gemini' => true,
            'grok' => true,
            'perplexity' => false,
        ],
        'social_networks' => [
            'twitter' => false,
            'linkedin' => false,
            'reddit' => false,
            'facebook' => false,
            'whatsapp' => false,
            'email' => false,
        ],
        'custom_networks' => [],
        'prompts' => [
            'Key takeaways' => 'Synthesize the core message of this article {url} and provide the 5 most impactful insights in a clear, bulleted format.',
            'Explain principles' => 'Identify the fundamental concepts in this article {url} and explain how they work together to solve a problem or create value.',
            'Create action plan' => 'Based on the insights from {url}, outline a practical, step-by-step plan for someone to implement these ideas today.',
            'Future perspectives' => 'Analyze the broader implications of this article {url}. How do these ideas connect to emerging trends, and what is the potential long-term impact?',
        ],
        'auto_insert' => false,
        'post_types' => ['post'],
        'position' => 'after',
        'disable_css' => false,
        'ai_label' => 'Explore with AI',
        'social_label' => 'Share',
    ];
}

/**
 * Get plugin settings
 */
function kaais_get_settings() {
    $defaults = kaais_get_defaults();
    $settings = get_option('kaais_settings', []);
    return wp_parse_args($settings, $defaults);
}

/**
 * Get AI platform definitions
 */
function kaais_get_ai_platforms() {
    $platforms = [
        'chatgpt' => [
            'name' => 'ChatGPT',
            'url' => 'https://chat.openai.com/?q={prompt}',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="20" height="20"><path d="M21.5498 10.0042C21.7995 9.26384 21.886 8.47826 21.8035 7.70133C21.721 6.92441 21.4714 6.17453 21.0718 5.50316C19.8548 3.41316 17.4098 2.33716 15.0218 2.84316C14.4928 2.25756 13.8459 1.79057 13.1236 1.47292C12.4013 1.15527 11.6198 0.99415 10.8308 1.00016C8.38976 0.995162 6.22376 2.54616 5.47276 4.83816C4.70082 4.99269 3.97055 5.30959 3.33041 5.76785C2.69027 6.2261 2.15488 6.81523 1.75976 7.49616C1.16254 8.51042 0.907301 9.68985 1.03173 10.8603C1.15615 12.0307 1.65365 13.1301 2.45076 13.9962C2.20077 14.7366 2.11397 15.5223 2.19631 16.2994C2.27864 17.0765 2.52817 17.8266 2.92776 18.4982C4.14476 20.5882 6.58976 21.6632 8.97776 21.1582C9.5065 21.7437 10.1533 22.2105 10.8754 22.528C11.5976 22.8455 12.3789 23.0064 13.1678 23.0002C15.6108 23.0062 17.7778 21.4542 18.5288 19.1602C19.3012 19.0057 20.032 18.6886 20.6725 18.23C21.313 17.7713 21.8486 17.1817 22.2438 16.5002C22.8399 15.486 23.0943 14.3071 22.9695 13.1373C22.8447 11.9675 22.3474 10.8688 21.5508 10.0032L21.5498 10.0042ZM13.1688 21.5622C12.1934 21.5645 11.2476 21.2272 10.4938 20.6082C10.5278 20.5902 10.5868 20.5582 10.6258 20.5342L15.0658 18.0042C15.1766 17.9423 15.2688 17.8519 15.3328 17.7423C15.3968 17.6328 15.4303 17.5081 15.4298 17.3812V11.2052L17.3068 12.2742C17.3268 12.2842 17.3398 12.3032 17.3428 12.3242V17.4392C17.3398 19.7132 15.4728 21.5572 13.1688 21.5622ZM4.19176 17.7802C3.70239 16.9477 3.52582 15.9681 3.69376 15.0172C3.72576 15.0372 3.78376 15.0722 3.82476 15.0952L8.26476 17.6252C8.48976 17.7552 8.76876 17.7552 8.99476 17.6252L14.4148 14.5372V16.6752C14.4152 16.6861 14.413 16.6971 14.4083 16.707C14.4036 16.7169 14.3965 16.7255 14.3878 16.7322L9.89976 19.2882C7.90076 20.4242 5.34776 19.7482 4.19276 17.7782L4.19176 17.7802ZM3.02276 8.21616C3.51405 7.37816 4.28374 6.73906 5.19776 6.41016L5.19576 6.56116V11.6212C5.19515 11.7482 5.22859 11.8731 5.29261 11.9828C5.35662 12.0926 5.44887 12.1832 5.55976 12.2452L10.9798 15.3322L9.10376 16.4022C9.09449 16.4081 9.08388 16.4117 9.07289 16.4126C9.0619 16.4134 9.05087 16.4116 9.04076 16.4072L4.55176 13.8482C2.55676 12.7082 1.87276 10.1902 3.02176 8.21816L3.02276 8.21616ZM18.4398 11.7562L13.0198 8.66816L14.8958 7.60016C14.905 7.59405 14.9155 7.5903 14.9265 7.58925C14.9375 7.58821 14.9486 7.58989 14.9588 7.59416L19.4478 10.1512C21.4458 11.2912 22.1308 13.8132 20.9768 15.7842C20.4851 16.6216 19.716 17.2608 18.8028 17.5912V12.3802C18.8034 12.2534 18.7701 12.1287 18.7062 12.0192C18.6424 11.9096 18.5504 11.8191 18.4398 11.7572V11.7562ZM20.3068 8.98316C20.2631 8.9566 20.2191 8.9306 20.1748 8.90516L15.7348 6.37516C15.6239 6.31137 15.4982 6.2778 15.3703 6.2778C15.2423 6.2778 15.1167 6.31137 15.0058 6.37516L9.58576 9.46316V7.32516C9.58533 7.31419 9.58755 7.30327 9.59226 7.29334C9.59696 7.28342 9.60399 7.27478 9.61276 7.26816L14.0998 4.71316C16.0998 3.57616 18.6548 4.25316 19.8068 6.22616C20.2938 7.05916 20.4718 8.03516 20.3068 8.98316ZM8.56576 12.7932L6.68876 11.7252C6.67897 11.7204 6.67053 11.7132 6.66424 11.7043C6.65795 11.6954 6.654 11.685 6.65276 11.6742V6.55916C6.65376 4.28216 8.52576 2.43716 10.8338 2.43916C11.8098 2.43916 12.7538 2.77716 13.5048 3.39316C13.4708 3.41116 13.4128 3.44316 13.3738 3.46616L8.93376 5.99616C8.82277 6.05787 8.73035 6.14822 8.66616 6.2578C8.60196 6.36738 8.56833 6.49217 8.56876 6.61916L8.56576 12.7922V12.7932ZM9.58576 10.6252L11.9998 9.25016L14.4138 10.6252V13.3752L11.9998 14.7502L9.58476 13.3752V10.6252H9.58576Z"/></svg>',
        ],
        'claude' => [
            'name' => 'Claude',
            'url' => 'https://claude.ai/new?q={prompt}',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="20" height="20"><path d="M4.709 15.955l4.72-2.647.08-.23-.08-.128H9.2l-.79-.048-2.698-.073-2.339-.097-2.266-.122-.571-.121L0 11.784l.055-.352.48-.321.686.06 1.52.103 2.278.158 1.652.097 2.449.255h.389l.055-.157-.134-.098-.103-.097-2.358-1.596-2.552-1.688-1.336-.972-.724-.491-.364-.462-.158-1.008.656-.722.881.06.225.061.893.686 1.908 1.476 2.491 1.833.365.304.145-.103.019-.073-.164-.274-1.355-2.446-1.446-2.49-.644-1.032-.17-.619a2.97 2.97 0 01-.104-.729L6.283.134 6.696 0l.996.134.42.364.62 1.414 1.002 2.229 1.555 3.03.456.898.243.832.091.255h.158V9.01l.128-1.706.237-2.095.23-2.695.08-.76.376-.91.747-.492.584.28.48.685-.067.444-.286 1.851-.559 2.903-.364 1.942h.212l.243-.242.985-1.306 1.652-2.064.73-.82.85-.904.547-.431h1.033l.76 1.129-.34 1.166-1.064 1.347-.881 1.142-1.264 1.7-.79 1.36.073.11.188-.02 2.856-.606 1.543-.28 1.841-.315.833.388.091.395-.328.807-1.969.486-2.309.462-3.439.813-.042.03.049.061 1.549.146.662.036h1.622l3.02.225.79.522.474.638-.079.485-1.215.62-1.64-.389-3.829-.91-1.312-.329h-.182v.11l1.093 1.068 2.006 1.81 2.509 2.33.127.578-.322.455-.34-.049-2.205-1.657-.851-.747-1.926-1.62h-.128v.17l.444.649 2.345 3.521.122 1.08-.17.353-.608.213-.668-.122-1.374-1.925-1.415-2.167-1.143-1.943-.14.08-.674 7.254-.316.37-.729.28-.607-.461-.322-.747.322-1.476.389-1.924.315-1.53.286-1.9.17-.632-.012-.042-.14.018-1.434 1.967-2.18 2.945-1.726 1.845-.414.164-.717-.37.067-.662.401-.589 2.388-3.036 1.44-1.882.93-1.086-.006-.158h-.055L4.132 18.56l-1.13.146-.487-.456.061-.746.231-.243 1.908-1.312-.006.006z"/></svg>',
        ],
        'gemini' => [
            'name' => 'Gemini',
            'url' => 'https://gemini.google.com/app?q={prompt}',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="20" height="20"><path d="M12 0C12 6.627 6.627 12 0 12c6.627 0 12 5.373 12 12 0-6.627 5.373-12 12-12-6.627 0-12-5.373-12-12z"/></svg>',
        ],
        'grok' => [
            'name' => 'Grok',
            'url' => 'https://x.com/i/grok?text={prompt}',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="20" height="20"><path d="M9.271 15.266L17.249 9.378C17.6405 9.09 18.2 9.201 18.386 9.6505C19.367 12.0145 18.928 14.856 16.9765 16.808C15.025 18.7585 12.31 19.186 9.827 18.212L7.1155 19.4675C11.0045 22.125 15.7275 21.4675 18.678 18.516C21.019 16.1765 21.7435 12.987 21.0655 10.11L21.071 10.1155C20.088 5.8895 21.312 4.201 23.8215 0.748C23.8795 0.666 23.94 0.583 24 0.5L20.699 3.7995V3.7885L9.269 15.2675M7.624 16.696C4.833 14.0315 5.3145 9.9065 7.695 7.5265C9.4555 5.7655 12.342 5.0475 14.8605 6.103L17.5665 4.8545C17.0795 4.5025 16.4545 4.1245 15.737 3.8575C12.498 2.5245 8.618 3.1875 5.9845 5.8185C3.452 8.3505 2.655 12.244 4.0225 15.5665C5.0445 18.049 3.369 19.8065 1.6815 21.578C1.082 22.2055 0.4835 22.835 0 23.5L7.6205 16.696"/></svg>',
        ],
        'perplexity' => [
            'name' => 'Perplexity',
            'url' => 'https://www.perplexity.ai/search?q={prompt}',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="20" height="20"><path d="M19.785 0V7.272H22.5V17.62H19.565V24L12.528 17.806V23.951H11.437V17.799L4.392 24V17.535H1.5V7.188H4.384V0L11.437 6.494V0.19H12.527V6.68L19.785 0ZM12.528 9.044V16.363L18.474 21.597V14.44L12.528 9.044ZM11.429 8.964L5.483 14.362V21.597L11.429 16.363V8.965V8.964ZM19.565 16.544H21.409V8.349H13.46L19.565 13.889V16.544ZM10.583 8.264H2.59V16.459H4.39V13.883L10.582 8.263L10.583 8.264ZM5.475 2.476V7.186H10.59L5.475 2.476ZM18.694 2.476L13.579 7.186H18.694V2.476Z"/></svg>',
        ],
    ];

    return apply_filters('kaais_ai_platforms', $platforms);
}

/**
 * Get social network definitions
 */
function kaais_get_social_networks() {
    $networks = [
        'twitter' => [
            'name' => 'X',
            'url' => 'https://x.com/intent/tweet?text={title}&url={url}',
            'icon' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>',
        ],
        'linkedin' => [
            'name' => 'LinkedIn',
            'url' => 'https://www.linkedin.com/shareArticle?mini=true&url={url}&title={title}',
            'icon' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>',
        ],
        'reddit' => [
            'name' => 'Reddit',
            'url' => 'https://reddit.com/submit?url={url}&title={title}',
            'icon' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0zm5.01 4.744c.688 0 1.25.561 1.25 1.249a1.25 1.25 0 0 1-2.498.056l-2.597-.547-.8 3.747c1.824.07 3.48.632 4.674 1.488.308-.309.73-.491 1.207-.491.968 0 1.754.786 1.754 1.754 0 .716-.435 1.333-1.01 1.614a3.111 3.111 0 0 1 .042.52c0 2.694-3.13 4.87-7.004 4.87-3.874 0-7.004-2.176-7.004-4.87 0-.183.015-.366.043-.534A1.748 1.748 0 0 1 4.028 12c0-.968.786-1.754 1.754-1.754.463 0 .898.196 1.207.49 1.207-.883 2.878-1.43 4.744-1.487l.885-4.182a.342.342 0 0 1 .14-.197.35.35 0 0 1 .238-.042l2.906.617a1.214 1.214 0 0 1 1.108-.701zM9.25 12C8.561 12 8 12.562 8 13.25c0 .687.561 1.248 1.25 1.248.687 0 1.248-.561 1.248-1.249 0-.688-.561-1.249-1.249-1.249zm5.5 0c-.687 0-1.248.561-1.248 1.25 0 .687.561 1.248 1.249 1.248.688 0 1.249-.561 1.249-1.249 0-.687-.562-1.249-1.25-1.249zm-5.466 3.99a.327.327 0 0 0-.231.094.33.33 0 0 0 0 .463c.842.842 2.484.913 2.961.913.477 0 2.105-.056 2.961-.913a.361.361 0 0 0 .029-.463.33.33 0 0 0-.464 0c-.547.533-1.684.73-2.512.73-.828 0-1.979-.196-2.512-.73a.326.326 0 0 0-.232-.095z"/></svg>',
        ],
        'facebook' => [
            'name' => 'Facebook',
            'url' => 'https://www.facebook.com/sharer/sharer.php?u={url}',
            'icon' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>',
        ],
        'whatsapp' => [
            'name' => 'WhatsApp',
            'url' => 'https://wa.me/?text={title}%20{url}',
            'icon' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>',
        ],
        'email' => [
            'name' => 'Email',
            'url' => 'mailto:?subject={title}&body={url}',
            'icon' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>',
        ],
    ];

    return apply_filters('kaais_social_networks', $networks);
}

/**
 * Render share buttons
 */
function kaais_render_buttons($post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }

    if (!$post_id) {
        return '';
    }

    $settings = kaais_get_settings();
    $ai_platforms = kaais_get_ai_platforms();
    $social_networks = kaais_get_social_networks();
    $prompts = apply_filters('kaais_prompts', $settings['prompts']);

    $post_url = get_permalink($post_id);
    $post_title = get_the_title($post_id);
    $encoded_url = rawurlencode($post_url);
    $encoded_title = rawurlencode($post_title);

    // Check if any AI platform is enabled
    $has_ai = false;
    foreach ($settings['ai_platforms'] as $id => $enabled) {
        if ($enabled && isset($ai_platforms[$id])) {
            $has_ai = true;
            break;
        }
    }

    // Check custom networks
    if (!$has_ai && !empty($settings['custom_networks'])) {
        $has_ai = true;
    }

    // Check if any social network is enabled
    $has_social = false;
    foreach ($settings['social_networks'] as $id => $enabled) {
        if ($enabled && isset($social_networks[$id])) {
            $has_social = true;
            break;
        }
    }

    if (!$has_ai && !$has_social) {
        return '';
    }

    ob_start();

    do_action('kaais_before_buttons');

    echo '<div class="kaais">';

    // AI Section
    if ($has_ai) {
        do_action('kaais_before_ai_section');

        echo '<div class="kaais__ai">';
        echo '<span class="kaais__label">' . esc_html($settings['ai_label']) . '</span>';
        echo '<div class="kaais__buttons">';

        // Built-in AI platforms
        foreach ($settings['ai_platforms'] as $id => $enabled) {
            if (!$enabled || !isset($ai_platforms[$id])) {
                continue;
            }

            $platform = $ai_platforms[$id];
            echo '<div class="kaais__dropdown" data-platform="' . esc_attr($id) . '">';
            echo '<button class="kaais__trigger" aria-label="' . esc_attr($platform['name']) . ' options" aria-expanded="false" aria-haspopup="menu">';
            echo $platform['icon'];
            echo '</button>';
            echo '<div class="kaais__menu" role="menu">';
            echo '<span class="kaais__menu-header">' . esc_html($platform['name']) . '</span>';

            foreach ($prompts as $label => $prompt_text) {
                $full_prompt = str_replace('{url}', $post_url, $prompt_text);
                $encoded_prompt = rawurlencode($full_prompt);
                $url = str_replace('{prompt}', $encoded_prompt, $platform['url']);

                echo '<a href="' . esc_url($url) . '" target="_blank" rel="noopener noreferrer" class="kaais__menu-item" role="menuitem">';
                echo esc_html($label);
                echo '</a>';
            }

            echo '</div>';
            echo '</div>';
        }

        // Custom networks
        if (!empty($settings['custom_networks'])) {
            foreach ($settings['custom_networks'] as $custom) {
                if (empty($custom['name']) || empty($custom['url_template'])) {
                    continue;
                }

                $custom_id = sanitize_title($custom['name']);
                echo '<div class="kaais__dropdown" data-platform="' . esc_attr($custom_id) . '">';
                echo '<button class="kaais__trigger" aria-label="' . esc_attr($custom['name']) . ' options" aria-expanded="false" aria-haspopup="menu">';

                if (!empty($custom['icon'])) {
                    echo '<img src="' . esc_url($custom['icon']) . '" alt="" width="20" height="20" />';
                } else {
                    echo '<span class="kaais__icon-placeholder">' . esc_html(mb_substr($custom['name'], 0, 1)) . '</span>';
                }

                echo '</button>';
                echo '<div class="kaais__menu" role="menu">';
                echo '<span class="kaais__menu-header">' . esc_html($custom['name']) . '</span>';

                foreach ($prompts as $label => $prompt_text) {
                    $full_prompt = str_replace('{url}', $post_url, $prompt_text);
                    $encoded_prompt = rawurlencode($full_prompt);
                    $url = str_replace('{prompt}', $encoded_prompt, $custom['url_template']);

                    echo '<a href="' . esc_url($url) . '" target="_blank" rel="noopener noreferrer" class="kaais__menu-item" role="menuitem">';
                    echo esc_html($label);
                    echo '</a>';
                }

                echo '</div>';
                echo '</div>';
            }
        }

        echo '</div>';
        echo '</div>';

        do_action('kaais_after_ai_section');
    }

    // Social Section
    if ($has_social) {
        do_action('kaais_before_social_section');

        echo '<div class="kaais__social">';
        echo '<span class="kaais__label">' . esc_html($settings['social_label']) . '</span>';
        echo '<div class="kaais__links">';

        foreach ($settings['social_networks'] as $id => $enabled) {
            if (!$enabled || !isset($social_networks[$id])) {
                continue;
            }

            $network = $social_networks[$id];
            $url = $network['url'];
            $url = str_replace('{url}', $encoded_url, $url);
            $url = str_replace('{title}', $encoded_title, $url);

            echo '<a href="' . esc_url($url) . '" target="_blank" rel="noopener noreferrer" aria-label="' . esc_attr(sprintf(__('Share on %s', 'kaais'), $network['name'])) . '">';
            echo $network['icon'];
            echo '</a>';
        }

        echo '</div>';
        echo '</div>';

        do_action('kaais_after_social_section');
    }

    echo '</div>';

    do_action('kaais_after_buttons');

    $output = ob_get_clean();

    return apply_filters('kaais_output', $output, $post_id);
}

/**
 * Shortcode handler
 */
function kaais_shortcode($atts) {
    $atts = shortcode_atts([
        'post_id' => null,
    ], $atts, 'kaais_share_buttons');

    return kaais_render_buttons($atts['post_id']);
}
add_shortcode('kaais_share_buttons', 'kaais_shortcode');

/**
 * Public function for theme developers
 */
function kaais_share_buttons($post_id = null, $echo = true) {
    $output = kaais_render_buttons($post_id);

    if ($echo) {
        echo $output;
    }

    return $output;
}

/**
 * Auto-insert into content
 */
function kaais_auto_insert($content) {
    if (!is_singular()) {
        return $content;
    }

    $settings = kaais_get_settings();

    if (!$settings['auto_insert']) {
        return $content;
    }

    $post_type = get_post_type();
    if (!in_array($post_type, $settings['post_types'], true)) {
        return $content;
    }

    $buttons = kaais_render_buttons();

    if (empty($buttons)) {
        return $content;
    }

    switch ($settings['position']) {
        case 'before':
            return $buttons . $content;
        case 'both':
            return $buttons . $content . $buttons;
        case 'after':
        default:
            return $content . $buttons;
    }
}
add_filter('the_content', 'kaais_auto_insert', 20);

/**
 * Enqueue frontend styles
 */
function kaais_enqueue_styles() {
    $settings = kaais_get_settings();

    if ($settings['disable_css']) {
        return;
    }

    wp_enqueue_style(
        'kaais-frontend',
        KAAIS_URL . 'assets/css/kaais-frontend.css',
        [],
        KAAIS_VERSION
    );
}
add_action('wp_enqueue_scripts', 'kaais_enqueue_styles');

/**
 * Load admin settings
 */
if (is_admin()) {
    require_once KAAIS_PATH . 'includes/class-kaais-settings.php';
}

/**
 * Plugin activation
 */
function kaais_activate() {
    if (!get_option('kaais_settings')) {
        add_option('kaais_settings', kaais_get_defaults());
    }
}
register_activation_hook(__FILE__, 'kaais_activate');
