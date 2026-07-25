<?php
/**
 * Unified Premium Hero Section Component (Refactored)
 * Styled dynamically based on advanced page-level Hero parameters.
 */
if (!empty($page['hero_title'])):
    // 1. Status Check (If inactive, skip rendering entirely)
    $heroStatus = (int)($page['hero_status'] ?? 1);
    if ($heroStatus === 0) {
        return;
    }

    $variant = $page['hero_variant'] ?? 'hero_split_large_image';
    $imageLayout = $page['hero_image_layout'] ?? 'right';
    $imageSize = $page['hero_image_size'] ?? 'large';
    $badge = $page['hero_badge'] ?? '';
    $visualMaxWidth = '480px'; // default, overridden per split variant below
    
    // Determine background style
    $bgStyle = !empty($page['hero_bg_color']) ? "background: " . $page['hero_bg_color'] . ";" : "";
    
    // Advanced UI Parameter extractions with defaults
    $heroLayoutMode = $page['hero_layout_mode'] ?? 'moyen';
    $heroTextPosition = $page['hero_text_position'] ?? 'centre'; // Vertical positioning: haut | milieu | bas
    $heroTextAlignment = $page['hero_text_alignment'] ?? 'center'; // Horizontal: left | center | right
    $heroTextWidth = $page['hero_text_width'] ?? '100%';
    $heroOverlayOpacity = isset($page['hero_overlay_opacity']) ? (float)$page['hero_overlay_opacity'] : 0.45;
    $heroShadowStrength = $page['hero_shadow_strength'] ?? 'moyen';
    $heroImageMobile = $page['hero_image_mobile'] ?? '';

    // Decode responsive settings JSON
    $respSettings = [];
    if (!empty($page['responsive_settings'])) {
        $respSettings = json_decode($page['responsive_settings'], true) ?: [];
    }

    // Extract visual adjustments
    $visualSettings = $respSettings['visual'] ?? [];
    $imgBrightness = isset($visualSettings['brightness']) ? (float)$visualSettings['brightness'] : 1.0;
    $imgSaturation = isset($visualSettings['saturation']) ? (float)$visualSettings['saturation'] : 1.0;
    $imgBlur = isset($visualSettings['blur']) ? (int)$visualSettings['blur'] : 0;
    
    $imageFilter = "filter: brightness({$imgBrightness}) saturate({$imgSaturation}) blur({$imgBlur}px);";

    // Size Mapping
    $sizeStyle = "width: 100%; max-width: 520px;";
    if ($imageSize === 'medium') {
        $sizeStyle = "width: 80%; max-width: 420px;";
    } elseif ($imageSize === 'small') {
        $sizeStyle = "width: 60%; max-width: 320px;";
    }

    // Grid Column Ordering
    $gridOrder = "grid-template-columns: 1.1fr 0.9fr;";
    $textOrder = "order: 1;";
    $visualOrder = "order: 2;";
    
    if ($imageLayout === 'left') {
        $gridOrder = "grid-template-columns: 0.9fr 1.1fr;";
        $textOrder = "order: 2;";
        $visualOrder = "order: 1;";
    }

    // Map Layout Heights and Paddings
    $heroMinHeight = '65vh';
    $heroPadding = '140px 0 80px 0';
    if ($heroLayoutMode === 'grand') {
        $heroMinHeight = '80vh';
        $heroPadding = '180px 0 120px 0';
    } elseif ($heroLayoutMode === 'compact') {
        $heroMinHeight = '50vh';
        $heroPadding = '100px 0 60px 0';
    } elseif ($heroLayoutMode === 'plein' || $heroLayoutMode === 'plein écran') {
        $heroMinHeight = '100vh';
        $heroPadding = '200px 0 120px 0';
    }

    // Map Horizontal Alignment margins
    $textMarginCss = "margin: 0 auto;";
    if ($heroTextAlignment === 'left' || $heroTextAlignment === 'gauche') {
        $textMarginCss = "margin-right: auto; margin-left: 0;";
    } elseif ($heroTextAlignment === 'right' || $heroTextAlignment === 'droite') {
        $textMarginCss = "margin-left: auto; margin-right: 0;";
    }
    $textAlignmentCss = "text-align: {$heroTextAlignment};";

    // Map Vertical Alignment
    $verticalAlignCss = "align-items: center;";
    if ($heroTextPosition === 'haut') {
        $verticalAlignCss = "align-items: flex-start;";
    } elseif ($heroTextPosition === 'bas') {
        $verticalAlignCss = "align-items: flex-end;";
    }

    // Text Shadow strengths
    $shadowStrengthCss = 'none';
    if ($heroShadowStrength === 'leger') {
        $shadowStrengthCss = '0 2px 4px rgba(0, 0, 0, 0.2)';
    } elseif ($heroShadowStrength === 'moyen') {
        $shadowStrengthCss = '0 4px 12px rgba(0, 0, 0, 0.4)';
    } elseif ($heroShadowStrength === 'fort') {
        $shadowStrengthCss = '0 8px 24px rgba(0, 0, 0, 0.6), 0 2px 6px rgba(0, 0, 0, 0.4)';
    }
?>

<style>
    #hero-section-<?= $page['id'] ?> h1, 
    #hero-section-<?= $page['id'] ?> p,
    #hero-section-<?= $page['id'] ?> .hero-brand-badge {
        text-shadow: <?= $shadowStrengthCss ?>;
    }

    /* Keyframes and custom styles for new premium Hero variants */
    @keyframes pulseGlow {
        0%, 100% { transform: translate(-50%, -50%) scale(1); opacity: 0.8; }
        50% { transform: translate(-50%, -50%) scale(1.08); opacity: 1; }
    }
    @keyframes floatGlow {
        0%, 100% { transform: translateY(0) scale(1); }
        50% { transform: translateY(-15px) scale(1.05); }
    }
    .glass-stack-card:hover {
        transform: scale(1.05) translateY(-2px) !important;
        border-color: rgba(99, 102, 241, 0.45) !important;
        box-shadow: 0 20px 40px rgba(99, 102, 241, 0.1) !important;
    }
    .glass-feature-card:hover {
        transform: translateY(-5px) !important;
        border-color: rgba(99, 102, 241, 0.4) !important;
        box-shadow: 0 20px 40px rgba(99, 102, 241, 0.15) !important;
        background: rgba(255, 255, 255, 0.07) !important;
    }

    /* Embedded Breakpoint styling overrides */
    <?php
    $mobileHeroTextPos = $respSettings['mobile']['hero_text_position'] ?? '';
    $mobileHeroTextAlign = $respSettings['mobile']['hero_text_alignment'] ?? '';
    $mobileHeroLayout = $respSettings['mobile']['hero_layout_mode'] ?? '';
    $mobileHeroShadow = $respSettings['mobile']['hero_shadow_strength'] ?? '';
    ?>
    @media (max-width: 768px) {
        <?php if (!empty($mobileHeroTextAlign)): ?>
        #hero-section-<?= $page['id'] ?> .hero-text-block {
            text-align: <?= $mobileHeroTextAlign ?> !important;
            align-items: <?= ($mobileHeroTextAlign === 'center' || $mobileHeroTextAlign === 'centre') ? 'center' : (($mobileHeroTextAlign === 'right' || $mobileHeroTextAlign === 'droite') ? 'flex-end' : 'flex-start') ?> !important;
            margin: <?= ($mobileHeroTextAlign === 'center' || $mobileHeroTextAlign === 'centre') ? '0 auto' : (($mobileHeroTextAlign === 'right' || $mobileHeroTextAlign === 'droite') ? 'margin-left: auto; margin-right: 0;' : 'margin-right: auto; margin-left: 0;') ?> !important;
        }
        <?php endif; ?>
        <?php if (!empty($mobileHeroTextPos)): ?>
        #hero-section-<?= $page['id'] ?> {
            align-items: <?= ($mobileHeroTextPos === 'haut') ? 'flex-start' : (($mobileHeroTextPos === 'bas') ? 'flex-end' : 'center') ?> !important;
        }
        <?php endif; ?>
        <?php if ($mobileHeroLayout === 'compact'): ?>
        #hero-section-<?= $page['id'] ?> {
            min-height: 45vh !important;
            padding: 90px 0 50px 0 !important;
        }
        <?php endif; ?>
        <?php if (!empty($mobileHeroShadow)): ?>
        #hero-section-<?= $page['id'] ?> h1, #hero-section-<?= $page['id'] ?> p {
            text-shadow: <?= ($mobileHeroShadow === 'fort') ? '0 8px 24px rgba(0,0,0,0.6)' : (($mobileHeroShadow === 'leger') ? '0 2px 4px rgba(0,0,0,0.2)' : 'none') ?> !important;
        }
        <?php endif; ?>
    }
</style>

<?php if ($variant === 'hero_slider'): 
    $slides = \App\Models\HeroSlide::getByPage($page['id']);
    if (empty($slides)) {
        // Fallback to page data as single slide
        $slides = [[
            'title' => $page['hero_title'],
            'subtitle' => $page['hero_subtitle'],
            'badge' => $page['hero_badge'],
            'image' => $page['hero_image'],
            'cta_text' => $page['hero_cta1_text'],
            'cta_url' => $page['hero_cta1_url']
        ]];
    }
?>
    <!-- Variant: Slider Dynamique -->
    <section class="premium-hero hero-slider-layout" id="hero-section-<?= $page['id'] ?>" style="position: relative; padding: <?= $heroPadding ?>; overflow: hidden; display: flex; <?= $verticalAlignCss ?> min-height: <?= $heroMinHeight ?>; background: #0b0f19;">
        
        <!-- Slides Wrapper -->
        <div class="slides-container" style="position: absolute; inset: 0; width: 100%; height: 100%;">
            <?php foreach ($slides as $idx => $slide): 
                $slideBg = !empty($slide['image']) 
                    ? "background: url('" . htmlspecialchars(url($slide['image'])) . "') center center / cover no-repeat;"
                    : (!empty($page['hero_bg_color']) ? "background: " . $page['hero_bg_color'] . ";" : "background: linear-gradient(135deg, #0b0f19 0%, #1e1b4b 100%);");
            ?>
                <div class="slide-item <?= $idx === 0 ? 'active' : '' ?>" data-index="<?= $idx ?>" style="position: absolute; inset: 0; width: 100%; height: 100%; transition: opacity 0.8s cubic-bezier(0.16, 1, 0.3, 1), transform 0.8s cubic-bezier(0.16, 1, 0.3, 1); opacity: <?= $idx === 0 ? '1' : '0' ?>; pointer-events: <?= $idx === 0 ? 'auto' : 'none' ?>; z-index: <?= $idx === 0 ? '2' : '1' ?>;">
                    
                    <!-- Background layer with CSS filters -->
                    <div class="hero-bg-layer" style="position: absolute; inset: 0; width: 100%; height: 100%; <?= $imageFilter ?> <?= $slideBg ?>"></div>
                    <!-- Overlay Veil -->
                    <div class="hero-overlay-veil" style="position: absolute; inset: 0; width: 100%; height: 100%; background: linear-gradient(rgba(11, 15, 25, <?= $heroOverlayOpacity ?>), rgba(11, 15, 25, <?= min(1.0, $heroOverlayOpacity + 0.2) ?>));"></div>

                    <div class="container" style="position: relative; height: 100%; display: flex; align-items: center; z-index: 5;">
                        <div class="hero-grid" style="display: grid; <?= $gridOrder ?> gap: 60px; align-items: center; width: 100%;">
                            
                            <!-- Slide Content -->
                            <div class="hero-text-block" style="<?= $textOrder ?> max-width: <?= $heroTextWidth ?>; <?= $textMarginCss ?> <?= $textAlignmentCss ?> display: flex; flex-direction: column; <?= ($heroTextAlignment === 'center' || $heroTextAlignment === 'centre') ? 'align-items: center;' : (($heroTextAlignment === 'right' || $heroTextAlignment === 'droite') ? 'align-items: flex-end;' : 'align-items: flex-start;') ?>">
                                <?php if (!empty($slide['badge'])): ?>
                                    <div class="hero-brand-badge" style="display: inline-flex; align-items: center; gap: 8px; background: rgba(99, 102, 241, 0.25); border: 1px solid rgba(99, 102, 241, 0.4); padding: 6px 18px; border-radius: 50px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.15em; color: #a5b4fc; margin-bottom: 28px; font-family: var(--font-headings);">
                                        <div style="width: 6px; height: 6px; border-radius: 50%; background: #818cf8; box-shadow: 0 0 8px #818cf8;"></div>
                                        <span><?= htmlspecialchars($slide['badge']) ?></span>
                                    </div>
                                <?php endif; ?>

                                <h1 style="font-size: clamp(2.4rem, 5.5vw, 4rem); line-height: 1.15; font-weight: 900; letter-spacing: -0.03em; color: #ffffff; margin-bottom: 24px; font-family: var(--font-headings);">
                                    <?= $slide['title'] ?>
                                </h1>

                                <div style="display: flex; gap: 6px; margin: 10px 0 24px 0;">
                                    <span style="width: 24px; height: 4px; border-radius: 2px; background: var(--primary);"></span>
                                    <span style="width: 16px; height: 4px; border-radius: 2px; background: var(--secondary);"></span>
                                </div>

                                <?php if (!empty($slide['subtitle'])): ?>
                                    <p style="font-size: 1.15rem; line-height: 1.7; color: #cbd5e1; margin-bottom: 36px; max-width: 620px; font-weight: 500;">
                                        <?= htmlspecialchars($slide['subtitle']) ?>
                                    </p>
                                <?php endif; ?>

                                <?php if (!empty($slide['cta_text'])): ?>
                                    <div style="display: flex; gap: 16px;">
                                        <a href="<?= htmlspecialchars(url($slide['cta_url'] ?? url('/contact'))) ?>" class="btn-cta-primary" style="display: inline-flex; align-items: center; gap: 10px; padding: 14px 32px; border-radius: 12px; font-weight: 700; color: white; background: linear-gradient(135deg, #e26d36 0%, #f97316 100%); border: 1px solid rgba(255,255,255,0.1); box-shadow: 0 10px 25px -5px rgba(226, 109, 54, 0.4); text-transform: uppercase; font-size: 0.82rem; letter-spacing: 0.05em; transition: var(--transition);">
                                            <span><?= htmlspecialchars($slide['cta_text']) ?></span>
                                            <i data-lucide="arrow-right" style="width: 16px; height: 16px;"></i>
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Slide Image visual -->
                            <div class="hero-right-visual" style="<?= $visualOrder ?> display: flex; justify-content: center; align-items: center;">
                                <div class="visual-wrap" style="position: relative; <?= $sizeStyle ?> max-width: <?= $visualMaxWidth ?>; filter: drop-shadow(0 25px 50px rgba(99, 102, 241, 0.25));">
                                    <div class="mockup-frame" style="background: rgba(255, 255, 255, 0.15); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.2); padding: 12px; border-radius: 24px; box-shadow: inset 0 1px 1px rgba(255,255,255,0.4);">
                                        <div class="screen-frame" style="aspect-ratio: 1.5; background: #0f172a; border-radius: 16px; overflow: hidden; border: 2px solid rgba(255,255,255,0.2); position: relative;">
                                            <?php if (!empty($slide['image'])): ?>
                                                <img src="<?= htmlspecialchars(url($slide['image'])) ?>" alt="Slide Visual" style="width: 100%; height: 100%; object-fit: cover; <?= $imageFilter ?>">
                                            <?php else: ?>
                                                <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #0b1329 0%, #1e1b4b 100%); display: flex; align-items: center; justify-content: center; color: white;">
                                                    <i data-lucide="image" style="width: 48px; height: 48px; color: rgba(255,255,255,0.3);"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Dots Indicators -->
        <?php if (count($slides) > 1): ?>
            <div class="slider-dots" style="position: absolute; bottom: 30px; left: 50%; transform: translateX(-50%); display: flex; gap: 8px; z-index: 10;">
                <?php foreach ($slides as $idx => $slide): ?>
                    <span class="slider-dot <?= $idx === 0 ? 'active' : '' ?>" data-target="<?= $idx ?>" style="width: 10px; height: 10px; border-radius: 50%; background: rgba(255,255,255,0.4); cursor: pointer; transition: all 0.3s;"></span>
                <?php endforeach; ?>
            </div>
            
            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    const heroSection = document.getElementById("hero-section-<?= $page['id'] ?>");
                    if (!heroSection) return;
                    
                    const slides = heroSection.querySelectorAll(".slide-item");
                    const dots = heroSection.querySelectorAll(".slider-dot");
                    let currentSlide = 0;
                    let slideInterval = null;
                    
                    function showSlide(index) {
                        slides.forEach((slide, idx) => {
                            if (idx === index) {
                                slide.style.opacity = "1";
                                slide.style.pointerEvents = "auto";
                                slide.style.zIndex = "2";
                                slide.classList.add("active");
                            } else {
                                slide.style.opacity = "0";
                                slide.style.pointerEvents = "none";
                                slide.style.zIndex = "1";
                                slide.classList.remove("active");
                            }
                        });
                        dots.forEach((dot, idx) => {
                            if (idx === index) {
                                dot.style.background = "#ffffff";
                                dot.style.transform = "scale(1.2)";
                                dot.classList.add("active");
                            } else {
                                dot.style.background = "rgba(255,255,255,0.4)";
                                dot.style.transform = "scale(1)";
                                dot.classList.remove("active");
                            }
                        });
                        currentSlide = index;
                    }
                    
                    function nextSlide() {
                        let next = (currentSlide + 1) % slides.length;
                        showSlide(next);
                    }
                    
                    function startAutoSlide() {
                        slideInterval = setInterval(nextSlide, 6000);
                    }
                    
                    function stopAutoSlide() {
                        if (slideInterval) clearInterval(slideInterval);
                    }
                    
                    dots.forEach((dot, idx) => {
                        dot.addEventListener("click", () => {
                            stopAutoSlide();
                            showSlide(idx);
                            startAutoSlide();
                        });
                    });
                    
                    showSlide(0);
                    startAutoSlide();
                });
            </script>
        <?php endif; ?>
    </section>

<?php elseif ($variant === 'hero_video'): 
    $videoUrl = $page['hero_image'] ?: '';
    $isVideo = str_ends_with(strtolower($videoUrl), '.mp4') || str_ends_with(strtolower($videoUrl), '.webm');
?>
    <!-- Variant: Background Video Hero -->
    <section class="premium-hero hero-video-layout" id="hero-section-<?= $page['id'] ?>" style="position: relative; padding: <?= $heroPadding ?>; overflow: hidden; display: flex; <?= $verticalAlignCss ?> min-height: <?= $heroMinHeight ?>; background: #000;">
        
        <!-- Video Background Layer -->
        <div class="hero-video-bg" style="position: absolute; inset: 0; width: 100%; height: 100%; z-index: 1;">
            <?php if ($isVideo): ?>
                <video autoplay muted loop playsinline style="width: 100%; height: 100%; object-fit: cover; <?= $imageFilter ?>">
                    <source src="<?= htmlspecialchars(url($videoUrl)) ?>" type="video/mp4">
                </video>
            <?php else: ?>
                <div style="width: 100%; height: 100%; background: url('<?= htmlspecialchars(url($videoUrl ?: '/assets/images/default_video_bg.jpg')) ?>') center center / cover no-repeat; <?= $imageFilter ?>"></div>
            <?php endif; ?>
            <div class="hero-overlay-veil" style="position: absolute; inset: 0; width: 100%; height: 100%; background: radial-gradient(circle, rgba(11,15,25,<?= $heroOverlayOpacity ?>) 0%, rgba(5,5,15,<?= min(1.0, $heroOverlayOpacity + 0.25) ?>) 100%);"></div>
        </div>

        <div class="container" style="position: relative; z-index: 5; width: 100%;">
            <div class="hero-text-block" style="max-width: <?= $heroTextWidth ?>; <?= $textMarginCss ?> <?= $textAlignmentCss ?> display: flex; flex-direction: column; <?= ($heroTextAlignment === 'center' || $heroTextAlignment === 'centre') ? 'align-items: center;' : (($heroTextAlignment === 'right' || $heroTextAlignment === 'droite') ? 'align-items: flex-end;' : 'align-items: flex-start;') ?> opacity: 0; transform: translateY(20px); animation: heroFadeIn 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;">
                
                <?php if (!empty($badge)): ?>
                    <div class="hero-brand-badge" style="display: inline-flex; align-items: center; gap: 8px; background: rgba(99, 102, 241, 0.2); border: 1px solid rgba(99, 102, 241, 0.3); padding: 6px 18px; border-radius: 50px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.15em; color: #a5b4fc; margin-bottom: 28px; font-family: var(--font-headings); box-shadow: 0 0 15px rgba(99, 102, 241, 0.15);">
                        <div style="width: 6px; height: 6px; border-radius: 50%; background: #818cf8;"></div>
                        <span><?= htmlspecialchars($badge) ?></span>
                    </div>
                <?php endif; ?>

                <h1 style="font-size: clamp(2.6rem, 6vw, 4.4rem); line-height: 1.1; font-weight: 900; letter-spacing: -0.03em; color: #ffffff; margin-bottom: 24px; font-family: var(--font-headings);">
                    <?= $page['hero_title'] ?>
                </h1>

                <div style="display: flex; gap: 6px; margin: 10px 0 28px 0;">
                    <span style="width: 28px; height: 4px; border-radius: 2px; background: var(--primary);"></span>
                    <span style="width: 18px; height: 4px; border-radius: 2px; background: var(--secondary);"></span>
                </div>

                <?php if (!empty($page['hero_subtitle'])): ?>
                    <p style="font-size: 1.15rem; line-height: 1.75; color: #cbd5e1; margin-bottom: 40px; max-width: 700px; font-weight: 500;">
                        <?= htmlspecialchars($page['hero_subtitle']) ?>
                    </p>
                <?php endif; ?>

                <div style="display: flex; gap: 16px; flex-wrap: wrap; justify-content: center; align-items: center;">
                    <?php if (!empty($page['hero_cta1_text'])): ?>
                        <a href="<?= htmlspecialchars($page['hero_cta1_url'] ?? '#contact') ?>" class="btn-cta-primary" style="display: inline-flex; align-items: center; gap: 10px; padding: 14px 34px; border-radius: 12px; font-weight: 700; color: white; background: linear-gradient(135deg, #e26d36 0%, #f97316 100%); border: 1px solid rgba(255,255,255,0.1); box-shadow: 0 10px 25px -5px rgba(226, 109, 54, 0.4); text-transform: uppercase; font-size: 0.82rem; letter-spacing: 0.05em; transition: var(--transition);">
                            <span><?= htmlspecialchars($page['hero_cta1_text']) ?></span>
                            <i data-lucide="arrow-right" style="width: 16px; height: 16px;"></i>
                        </a>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </section>

<?php elseif ($variant === 'hero_corporate'): ?>
    <!-- Variant: Corporate Hero -->
    <section class="premium-hero hero-corporate-layout" id="hero-section-<?= $page['id'] ?>" style="position: relative; padding: <?= $heroPadding ?>; overflow: hidden; display: flex; <?= $verticalAlignCss ?> min-height: <?= $heroMinHeight ?>; background: linear-gradient(to right, #f8fafc, #f1f5f9);">
        
        <div class="hero-glow" style="position: absolute; top: 0; right: 0; width: 500px; height: 100%; background: radial-gradient(circle, rgba(99, 102, 241, 0.04) 0%, transparent 80%); pointer-events: none;"></div>

        <div class="container" style="position: relative; z-index: 5; width: 100%;">
            <div class="hero-grid" style="display: grid; <?= $gridOrder ?> gap: 60px; align-items: center;">
                
                <div class="hero-text-block" style="<?= $textOrder ?> max-width: <?= $heroTextWidth ?>; <?= $textMarginCss ?> <?= $textAlignmentCss ?> display: flex; flex-direction: column; <?= ($heroTextAlignment === 'center' || $heroTextAlignment === 'centre') ? 'align-items: center;' : (($heroTextAlignment === 'right' || $heroTextAlignment === 'droite') ? 'align-items: flex-end;' : 'align-items: flex-start;') ?>">
                    <?php if (!empty($badge)): ?>
                        <span style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: var(--primary); letter-spacing: 0.1em; margin-bottom: 12px; font-family: var(--font-headings);"><?= htmlspecialchars($badge) ?></span>
                    <?php endif; ?>

                    <h1 style="font-size: clamp(2.4rem, 5vw, 3.8rem); line-height: 1.15; font-weight: 800; color: #0f172a; margin-bottom: 20px; font-family: var(--font-headings); letter-spacing: -0.02em;">
                        <?= $page['hero_title'] ?>
                    </h1>

                    <div style="width: 40px; height: 3px; background: var(--primary); margin: 8px 0 20px 0;"></div>

                    <?php if (!empty($page['hero_subtitle'])): ?>
                        <p style="font-size: 1.1rem; line-height: 1.7; color: #475569; margin-bottom: 32px; max-width: 580px; font-weight: 500;">
                            <?= htmlspecialchars($page['hero_subtitle']) ?>
                        </p>
                    <?php endif; ?>

                    <div style="display: flex; gap: 14px; flex-wrap: wrap;">
                        <?php if (!empty($page['hero_cta1_text'])): ?>
                            <a href="<?= htmlspecialchars(url($page['hero_cta1_url'] ?? '#contact')) ?>" class="btn-cta-primary" style="display: inline-flex; align-items: center; padding: 12px 28px; border-radius: 8px; font-weight: 700; color: white; background: #0f172a; border: 1px solid #0f172a; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.05em; transition: var(--transition);">
                                <span><?= htmlspecialchars($page['hero_cta1_text']) ?></span>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($page['hero_cta2_text'])): ?>
                            <a href="<?= htmlspecialchars(url($page['hero_cta2_url'] ?? '#services')) ?>" class="btn-cta-secondary" style="display: inline-flex; align-items: center; padding: 12px 26px; border-radius: 8px; font-weight: 600; color: #334155; background: transparent; border: 1px solid #cbd5e1; transition: var(--transition);">
                                <span><?= htmlspecialchars($page['hero_cta2_text']) ?></span>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="hero-right-visual" style="<?= $visualOrder ?> display: flex; justify-content: center; align-items: center;">
                    <div style="width: 100%; max-width: 480px; position: relative;">
                        <div style="background: white; border: 1px solid #e2e8f0; padding: 10px; border-radius: 16px; box-shadow: 0 20px 40px rgba(0,0,0,0.04);">
                            <div style="aspect-ratio: 1.4; border-radius: 10px; overflow: hidden; background: #f8fafc;">
                                <?php if (!empty($page['hero_image'])): ?>
                                    <img src="<?= htmlspecialchars(url($page['hero_image'])) ?>" alt="Corporate Visual" style="width: 100%; height: 100%; object-fit: cover; <?= $imageFilter ?>">
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

<?php elseif ($variant === 'hero_magazine'): ?>
    <!-- Variant: Magazine Hero -->
    <section class="premium-hero hero-magazine-layout" id="hero-section-<?= $page['id'] ?>" style="position: relative; padding: <?= $heroPadding ?>; overflow: hidden; display: flex; <?= $verticalAlignCss ?> min-height: <?= $heroMinHeight ?>; background: #ffffff;">
        
        <div class="container" style="position: relative; z-index: 5; width: 100%;">
            <div style="display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 80px; align-items: start;">
                
                <!-- Left Editorial Text Column -->
                <div class="hero-text-block" style="display: flex; flex-direction: column; align-items: start; text-align: left;">
                    <?php if (!empty($badge)): ?>
                        <span style="font-size: 0.72rem; text-transform: uppercase; font-weight: 800; color: var(--secondary); letter-spacing: 0.25em; border-bottom: 2px solid var(--secondary); padding-bottom: 4px; margin-bottom: 28px; font-family: var(--font-headings);"><?= htmlspecialchars($badge) ?></span>
                    <?php endif; ?>

                    <h1 style="font-size: clamp(2.8rem, 6.5vw, 4.2rem); line-height: 1.05; font-weight: 800; color: #000000; margin-bottom: 28px; font-family: var(--font-headings); letter-spacing: -0.04em;">
                        <?= $page['hero_title'] ?>
                    </h1>

                    <?php if (!empty($page['hero_subtitle'])): ?>
                        <p style="font-size: 1.2rem; line-height: 1.7; color: #334155; margin-bottom: 44px; font-weight: 500; font-family: var(--font-body);">
                            <?= htmlspecialchars($page['hero_subtitle']) ?>
                        </p>
                    <?php endif; ?>

                    <div style="display: flex; gap: 16px; align-items: center;">
                        <?php if (!empty($page['hero_cta1_text'])): ?>
                            <a href="<?= htmlspecialchars(url($page['hero_cta1_url'] ?? '#contact')) ?>" class="btn-cta-primary" style="display: inline-flex; align-items: center; gap: 10px; padding: 14px 36px; border-radius: 0; font-weight: 700; color: white; background: #000000; border: 1px solid #000000; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.1em; transition: var(--transition);">
                                <span><?= htmlspecialchars($page['hero_cta1_text']) ?></span>
                                <i data-lucide="arrow-right" style="width: 16px; height: 16px;"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Right Offset Magazine Columns/Sidebar -->
                <?php
                $magArticles = [];
                if (!empty($page['hero_articles'])) {
                    $magArticles = json_decode($page['hero_articles'], true) ?: [];
                }
                // Fallback: pull latest blog posts from DB
                if (empty($magArticles)) {
                    $latestPosts = \App\Services\Database::fetchAll("SELECT title, slug, category FROM blog_posts WHERE status = 'published' ORDER BY published_at DESC LIMIT 3") ?: [];
                    foreach ($latestPosts as $lp) {
                        $magArticles[] = [
                            'category' => strtoupper($lp['category'] ?? 'ACTUALITÉ'),
                            'title' => $lp['title'],
                            'url' => '/blog/' . $lp['slug'],
                        ];
                    }
                }
                if (empty($magArticles)) {
                    $magArticles = [
                        ['category' => 'TECHNOLOGIE', 'title' => 'Stratégies d\'intégration IA dans les grandes entreprises.', 'url' => '/blog'],
                        ['category' => 'DESIGN', 'title' => 'Le futur des Design Systems et du CMS sans code.', 'url' => '/blog'],
                        ['category' => 'DÉVELOPPEMENT', 'title' => 'Green IT et performances serveurs : optimiser les builds.', 'url' => '/blog'],
                    ];
                }
                $magColors = ['var(--secondary)', 'var(--primary)', '#e26d36'];
                ?>
                <div style="display: flex; flex-direction: column; gap: 32px; border-left: 1px solid #e2e8f0; padding-left: 40px; min-height: 250px;">
                    <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: #64748b; letter-spacing: 0.15em; font-family: var(--font-headings);">Dernières actualités</div>
                    <div style="display: flex; flex-direction: column; gap: 24px;">
                        <?php foreach (array_slice($magArticles, 0, 3) as $mi => $art): ?>
                        <div class="magazine-nav-item" style="display: flex; flex-direction: column; gap: 6px;">
                            <span style="font-size: 0.7rem; color: <?= $magColors[$mi % 3] ?>; font-weight: 700; font-family: var(--font-headings);"><?= htmlspecialchars(strtoupper($art['category'] ?? 'ACTUALITÉ')) ?></span>
                            <a href="<?= htmlspecialchars(url($art['url'] ?? '/blog')) ?>" style="font-size: 1.05rem; font-weight: 700; color: #000000; text-decoration: none; line-height: 1.3;"><?= htmlspecialchars($art['title'] ?? '') ?></a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

            </div>
        </div>
    </section>

<?php elseif ($variant === 'hero_card'): 
    $fullBgStyle = !empty($page['hero_image']) 
        ? "background: url('" . htmlspecialchars(url($page['hero_image'])) . "') center center / cover no-repeat;"
        : (!empty($page['hero_bg_color']) ? "background: " . $page['hero_bg_color'] . ";" : "background: linear-gradient(135deg, #0b0f19 0%, #111827 100%);");
?>
    <!-- Variant: Floating Glassmorphic Card Hero -->
    <section class="premium-hero hero-card-layout" id="hero-section-<?= $page['id'] ?>" style="position: relative; padding: <?= $heroPadding ?>; overflow: hidden; display: flex; <?= $verticalAlignCss ?> min-height: <?= $heroMinHeight ?>;">
        
        <div class="hero-bg-layer" style="position: absolute; inset: 0; z-index: 1; <?= $imageFilter ?> <?= $fullBgStyle ?>"></div>
        <div class="hero-overlay-veil" style="position: absolute; inset: 0; z-index: 2; background: rgba(11, 15, 25, <?= $heroOverlayOpacity ?>);"></div>

        <div class="container" style="position: relative; z-index: 5; width: 100%; display: flex; justify-content: center;">
            
            <!-- Floating Card -->
            <div class="floating-glass-card-hero" style="background: rgba(11,18,34,0.85); backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px); border: 1px solid rgba(255,255,255,0.1); padding: 48px 40px; border-radius: 24px; box-shadow: 0 30px 60px -15px rgba(0,0,0,0.5), 0 0 0 1px rgba(255,255,255,0.06); max-width: 800px; width: 100%; text-align: center; display: flex; flex-direction: column; align-items: center; animation: floatBadge 8s ease-in-out infinite;">
                
                <?php if (!empty($badge)): ?>
                    <span class="hero-brand-badge" style="display: inline-flex; align-items: center; gap: 8px; background: rgba(99, 102, 241, 0.12); border: 1px solid rgba(99, 102, 241, 0.25); padding: 6px 18px; border-radius: 50px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.15em; color: var(--primary); margin-bottom: 24px; font-family: var(--font-headings);"><?= htmlspecialchars($badge) ?></span>
                <?php endif; ?>

                <h1 style="font-size: clamp(2.2rem, 5vw, 3.6rem); line-height: 1.15; font-weight: 900; color: var(--text-main); margin-bottom: 20px; font-family: var(--font-headings); letter-spacing: -0.02em; text-shadow: none !important;">
                    <?= $page['hero_title'] ?>
                </h1>

                <div style="display: flex; gap: 6px; margin: 10px 0 20px 0;">
                    <span style="width: 24px; height: 4px; border-radius: 2px; background: var(--primary);"></span>
                    <span style="width: 16px; height: 4px; border-radius: 2px; background: var(--secondary);"></span>
                </div>

                <?php if (!empty($page['hero_subtitle'])): ?>
                    <p style="font-size: 1.12rem; line-height: 1.7; color: var(--text-sub); margin-bottom: 36px; max-width: 620px; font-weight: 500; text-shadow: none !important;">
                        <?= htmlspecialchars($page['hero_subtitle']) ?>
                    </p>
                <?php endif; ?>

                <div style="display: flex; gap: 16px; flex-wrap: wrap; justify-content: center; align-items: center;">
                    <?php if (!empty($page['hero_cta1_text'])): ?>
                        <a href="<?= htmlspecialchars(url($page['hero_cta1_url'] ?? '#contact')) ?>" class="btn-cta-primary" style="display: inline-flex; align-items: center; gap: 10px; padding: 14px 32px; border-radius: 12px; font-weight: 700; color: white; background: linear-gradient(135deg, #e26d36 0%, #f97316 100%); border: 1px solid rgba(255,255,255,0.1); box-shadow: 0 10px 25px -5px rgba(226, 109, 54, 0.4); text-transform: uppercase; font-size: 0.82rem; letter-spacing: 0.05em; transition: var(--transition);">
                            <span><?= htmlspecialchars($page['hero_cta1_text']) ?></span>
                            <i data-lucide="arrow-right" style="width: 16px; height: 16px;"></i>
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($page['hero_cta2_text'])): ?>
                        <a href="<?= htmlspecialchars(url($page['hero_cta2_url'] ?? '#services')) ?>" class="btn-cta-secondary" style="display: inline-flex; align-items: center; gap: 8px; padding: 14px 28px; border-radius: 12px; font-weight: 600; color: #0f172a; background: rgba(15,23,42,0.04); border: 1px solid rgba(15,23,42,0.08); transition: var(--transition);">
                            <span><?= htmlspecialchars($page['hero_cta2_text']) ?></span>
                        </a>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </section>

<?php elseif ($variant === 'hero_full_image' || $variant === 'hero_full_width'): 
    // Pleine Page with custom filters separated on background element to keep text sharp
    $fullBgStyle = !empty($page['hero_image']) 
        ? "background: url('" . htmlspecialchars(url($page['hero_image'])) . "') center center / cover no-repeat;"
        : (!empty($page['hero_bg_color']) ? "background: " . $page['hero_bg_color'] . ";" : "background: linear-gradient(135deg, #0b0f19 0%, #111827 100%);");
?>
    <!-- Variant 1: Full Background Image Hero -->
    <section class="premium-hero hero-full-bg" id="hero-section-<?= $page['id'] ?>" style="position: relative; padding: <?= $heroPadding ?>; overflow: hidden; display: flex; <?= $verticalAlignCss ?> min-height: <?= $heroMinHeight ?>;">
        
        <!-- Background Layer with CSS Filters -->
        <div class="hero-bg-layer" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 1; <?= $imageFilter ?>">
            <?php if (!empty($heroImageMobile)): ?>
                <picture style="width: 100%; height: 100%;">
                    <source srcset="<?= htmlspecialchars(url($heroImageMobile)) ?>" media="(max-width: 768px)">
                    <div style="width: 100%; height: 100%; <?= $fullBgStyle ?>"></div>
                </picture>
            <?php else: ?>
                <div style="width: 100%; height: 100%; <?= $fullBgStyle ?>"></div>
            <?php endif; ?>
            <!-- Overlay Veil -->
            <div class="hero-overlay-veil" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(rgba(11, 15, 25, <?= $heroOverlayOpacity ?>), rgba(11, 15, 25, <?= min(1.0, $heroOverlayOpacity + 0.15) ?>)); z-index: 2;"></div>
        </div>

        <!-- Ambient Radial Glows -->
        <div class="hero-glow-1" style="position: absolute; width: 600px; height: 600px; border-radius: 50%; background: radial-gradient(circle, rgba(99, 102, 241, 0.15) 0%, rgba(99, 102, 241, 0) 70%); top: -200px; right: -100px; z-index: 2; pointer-events: none;"></div>
        <div class="hero-glow-2" style="position: absolute; width: 600px; height: 600px; border-radius: 50%; background: radial-gradient(circle, rgba(236, 72, 153, 0.08) 0%, rgba(236, 72, 153, 0) 70%); bottom: -200px; left: -100px; z-index: 2; pointer-events: none;"></div>

        <div class="container" style="position: relative; z-index: 5; width: 100%;">
            <div class="hero-text-block" style="max-width: <?= $heroTextWidth ?>; <?= $textMarginCss ?> <?= $textAlignmentCss ?> display: flex; flex-direction: column; <?= ($heroTextAlignment === 'center' || $heroTextAlignment === 'centre') ? 'align-items: center;' : (($heroTextAlignment === 'right' || $heroTextAlignment === 'droite') ? 'align-items: flex-end;' : 'align-items: flex-start;') ?> opacity: 0; transform: translateY(20px); animation: heroFadeIn 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;">
                
                <!-- Badge / Label -->
                <?php if (!empty($badge)): ?>
                    <div class="hero-brand-badge" style="display: inline-flex; align-items: center; gap: 8px; background: rgba(99, 102, 241, 0.12); border: 1px solid rgba(99, 102, 241, 0.25); padding: 6px 18px; border-radius: 50px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.15em; color: #a5b4fc; margin-bottom: 28px; font-family: var(--font-heading); box-shadow: 0 0 15px rgba(99, 102, 241, 0.1);">
                        <div style="width: 6px; height: 6px; border-radius: 50%; background: #818cf8; box-shadow: 0 0 8px #818cf8;"></div>
                        <span><?= htmlspecialchars($badge) ?></span>
                    </div>
                <?php endif; ?>

                <!-- Heading -->
                <h1 style="font-size: clamp(2.6rem, 6vw, 4.4rem); line-height: 1.1; font-weight: 900; letter-spacing: -0.03em; color: #ffffff; margin-bottom: 24px; font-family: var(--font-heading);">
                    <?= $page['hero_title'] ?>
                </h1>

                <!-- Accent Stripes -->
                <div style="display: flex; gap: 6px; margin: 10px 0 28px 0;">
                    <span style="width: 28px; height: 4px; border-radius: 2px; background: var(--primary);"></span>
                    <span style="width: 18px; height: 4px; border-radius: 2px; background: var(--secondary);"></span>
                    <span style="width: 36px; height: 4px; border-radius: 2px; background: var(--accent);"></span>
                </div>

                <!-- Subtitle / Pitch (Ensured contrast with backdrop blur card) -->
                <?php if (!empty($page['hero_subtitle'])): ?>
                    <div style="background: rgba(15, 23, 42, 0.45); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.06); padding: 16px 28px; border-radius: 16px; margin-bottom: 40px; max-width: 700px; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
                        <p style="font-size: 1.12rem; line-height: 1.75; color: #e2e8f0; font-weight: 500; margin: 0;">
                            <?= htmlspecialchars($page['hero_subtitle']) ?>
                        </p>
                    </div>
                <?php endif; ?>

                <!-- Actions -->
                <div style="display: flex; gap: 16px; flex-wrap: wrap; justify-content: center; align-items: center;">
                    <?php if (!empty($page['hero_cta1_text'])): ?>
                        <a href="<?= htmlspecialchars($page['hero_cta1_url'] ?? '#contact') ?>" class="btn-cta-primary" style="display: inline-flex; align-items: center; gap: 10px; padding: 14px 34px; border-radius: 12px; font-weight: 700; color: white; background: linear-gradient(135deg, #e26d36 0%, #f97316 100%); border: 1px solid rgba(255,255,255,0.1); box-shadow: 0 10px 25px -5px rgba(226, 109, 54, 0.4); text-transform: uppercase; font-size: 0.82rem; letter-spacing: 0.05em; transition: var(--transition);">
                            <span><?= htmlspecialchars($page['hero_cta1_text']) ?></span>
                            <i data-lucide="arrow-right" style="width: 16px; height: 16px;"></i>
                        </a>
                    <?php endif; ?>

                    <?php if (!empty($page['hero_cta2_text'])): ?>
                        <a href="<?= htmlspecialchars($page['hero_cta2_url'] ?? '#services') ?>" class="btn-cta-secondary" style="display: inline-flex; align-items: center; gap: 8px; padding: 14px 30px; border-radius: 12px; font-weight: 600; color: #ffffff; background: rgba(255, 255, 255, 0.08); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); border: 1px solid rgba(255,255,255,0.15); box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); transition: var(--transition);">
                            <span><?= htmlspecialchars($page['hero_cta2_text']) ?></span>
                        </a>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </section>

<?php elseif ($variant === 'hero_text_only'): ?>
    <!-- Variant 2: Minimalist Text Only Hero -->
    <section class="premium-hero hero-text-only-layout" id="hero-section-<?= $page['id'] ?>" style="<?= $bgStyle ?> position: relative; padding: <?= $heroPadding ?>; overflow: hidden; display: flex; <?= $verticalAlignCss ?> min-height: <?= $heroMinHeight ?>;">
        
        <div class="hero-glow-1" style="position: absolute; width: 500px; height: 500px; border-radius: 50%; background: radial-gradient(circle, rgba(124, 58, 237, 0.08) 0%, rgba(124, 58, 237, 0) 70%); top: -100px; right: -50px; z-index: 1; pointer-events: none;"></div>

        <div class="container" style="position: relative; z-index: 5; width: 100%;">
            <div class="hero-text-block" style="max-width: <?= $heroTextWidth ?>; <?= $textMarginCss ?> <?= $textAlignmentCss ?> display: flex; flex-direction: column; <?= ($heroTextAlignment === 'center' || $heroTextAlignment === 'centre') ? 'align-items: center;' : (($heroTextAlignment === 'right' || $heroTextAlignment === 'droite') ? 'align-items: flex-end;' : 'align-items: flex-start;') ?> opacity: 0; transform: translateY(20px); animation: heroFadeIn 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;">
                
                <!-- Badge -->
                <?php if (!empty($badge)): ?>
                    <div class="hero-brand-badge" style="display: inline-flex; align-items: center; gap: 8px; background: rgba(124, 58, 237, 0.08); border: 1px solid rgba(124, 58, 237, 0.2); padding: 6px 16px; border-radius: 50px; font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.12em; color: var(--secondary); margin-bottom: 24px; font-family: var(--font-heading);">
                        <div style="width: 6px; height: 6px; border-radius: 50%; background: var(--secondary); box-shadow: 0 0 8px var(--secondary);"></div>
                        <span><?= htmlspecialchars($badge) ?></span>
                    </div>
                <?php endif; ?>

                <!-- Heading -->
                <h1 style="font-size: clamp(2.4rem, 5.5vw, 4rem); line-height: 1.15; font-weight: 900; letter-spacing: -0.02em; color: var(--text-main); margin-bottom: 20px; font-family: var(--font-heading);">
                    <?= $page['hero_title'] ?>
                </h1>

                <!-- Accent Stripes -->
                <div style="display: flex; gap: 6px; margin: 10px 0 24px 0;">
                    <span style="width: 24px; height: 4px; border-radius: 2px; background: var(--primary);"></span>
                    <span style="width: 16px; height: 4px; border-radius: 2px; background: var(--secondary);"></span>
                </div>

                <!-- Subtitle -->
                <?php if (!empty($page['hero_subtitle'])): ?>
                    <p style="font-size: 1.15rem; line-height: 1.7; color: var(--text-muted); margin-bottom: 36px; max-width: 620px; font-weight: 500;">
                        <?= htmlspecialchars($page['hero_subtitle']) ?>
                    </p>
                <?php endif; ?>

                <!-- Actions -->
                <div style="display: flex; gap: 16px; flex-wrap: wrap; justify-content: center; align-items: center;">
                    <?php if (!empty($page['hero_cta1_text'])): ?>
                        <a href="<?= htmlspecialchars($page['hero_cta1_url'] ?? '#contact') ?>" class="btn-cta-primary" style="display: inline-flex; align-items: center; gap: 10px; padding: 14px 32px; border-radius: 12px; font-weight: 700; color: white; background: linear-gradient(135deg, #e26d36 0%, #f97316 100%); border: 1px solid rgba(255,255,255,0.1); box-shadow: 0 10px 25px -5px rgba(226, 109, 54, 0.4); text-transform: uppercase; font-size: 0.82rem; letter-spacing: 0.05em; transition: var(--transition);">
                            <span><?= htmlspecialchars($page['hero_cta1_text']) ?></span>
                            <i data-lucide="arrow-right" style="width: 16px; height: 16px;"></i>
                        </a>
                    <?php endif; ?>

                    <?php if (!empty($page['hero_cta2_text'])): ?>
                        <a href="<?= htmlspecialchars($page['hero_cta2_url'] ?? '#services') ?>" class="btn-cta-secondary" style="display: inline-flex; align-items: center; gap: 8px; padding: 14px 28px; border-radius: 12px; font-weight: 600; color: var(--text-main); background: rgba(255,255,255,0.05); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); border: 1px solid var(--border-md); box-shadow: none; transition: var(--transition);">
                            <span><?= htmlspecialchars($page['hero_cta2_text']) ?></span>
                        </a>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </section>

<?php elseif ($variant === 'hero_ambient_glow'): ?>
    <!-- Variant 6: Ambient Glow Hero -->
    <section class="premium-hero hero-ambient-glow-layout" id="hero-section-<?= $page['id'] ?>" style="<?= $bgStyle ?> position: relative; padding: <?= $heroPadding ?>; overflow: hidden; display: flex; <?= $verticalAlignCss ?> min-height: <?= $heroMinHeight ?>;">
        
        <!-- Large neon glowing circles behind content -->
        <div class="ambient-glow-core" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 600px; height: 600px; border-radius: 50%; background: radial-gradient(circle, rgba(124, 58, 237, 0.18) 0%, rgba(30, 58, 138, 0.08) 40%, rgba(0,0,0,0) 70%); filter: blur(40px); z-index: 1; pointer-events: none; animation: pulseGlow 10s ease-in-out infinite;"></div>
        <div class="ambient-glow-secondary" style="position: absolute; top: 40%; left: 30%; width: 400px; height: 400px; border-radius: 50%; background: radial-gradient(circle, rgba(226, 109, 54, 0.12) 0%, rgba(226, 109, 54, 0) 70%); filter: blur(30px); z-index: 1; pointer-events: none; animation: floatGlow 12s ease-in-out infinite;"></div>

        <div class="container" style="position: relative; z-index: 5; width: 100%;">
            <div class="hero-text-block" style="max-width: <?= $heroTextWidth ?>; <?= $textMarginCss ?> <?= $textAlignmentCss ?> display: flex; flex-direction: column; <?= ($heroTextAlignment === 'center' || $heroTextAlignment === 'centre') ? 'align-items: center;' : (($heroTextAlignment === 'right' || $heroTextAlignment === 'droite') ? 'align-items: flex-end;' : 'align-items: flex-start;') ?> opacity: 0; transform: translateY(20px); animation: heroFadeIn 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;">
                
                <!-- Badge -->
                <?php if (!empty($badge)): ?>
                    <div class="hero-brand-badge" style="display: inline-flex; align-items: center; gap: 8px; background: rgba(124, 58, 237, 0.12); border: 1px solid rgba(124, 58, 237, 0.3); padding: 6px 18px; border-radius: 50px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.15em; color: #a5b4fc; margin-bottom: 28px; font-family: var(--font-heading); box-shadow: 0 0 20px rgba(124, 58, 237, 0.15);">
                        <div style="width: 6px; height: 6px; border-radius: 50%; background: #818cf8; box-shadow: 0 0 8px #818cf8;"></div>
                        <span><?= htmlspecialchars($badge) ?></span>
                    </div>
                <?php endif; ?>

                <!-- Heading -->
                <h1 style="font-size: clamp(2.6rem, 6.5vw, 4.5rem); line-height: 1.1; font-weight: 900; letter-spacing: -0.03em; color: var(--text-main); margin-bottom: 24px; font-family: var(--font-heading);">
                    <?= $page['hero_title'] ?>
                </h1>

                <!-- Accent Stripes -->
                <div style="display: flex; gap: 8px; margin: 10px 0 28px 0;">
                    <span style="width: 36px; height: 5px; border-radius: 2.5px; background: var(--primary); box-shadow: 0 0 10px rgba(99, 102, 241, 0.5);"></span>
                    <span style="width: 24px; height: 5px; border-radius: 2.5px; background: var(--secondary); box-shadow: 0 0 10px rgba(124, 58, 237, 0.5);"></span>
                </div>

                <!-- Subtitle -->
                <?php if (!empty($page['hero_subtitle'])): ?>
                    <p style="font-size: 1.2rem; line-height: 1.75; color: var(--text-muted); margin-bottom: 40px; max-width: 750px; font-weight: 500;">
                        <?= htmlspecialchars($page['hero_subtitle']) ?>
                    </p>
                <?php endif; ?>

                <!-- Actions -->
                <div style="display: flex; gap: 16px; flex-wrap: wrap; justify-content: center; align-items: center;">
                    <?php if (!empty($page['hero_cta1_text'])): ?>
                        <a href="<?= htmlspecialchars(url($page['hero_cta1_url'] ?? '#contact')) ?>" class="btn-cta-primary" style="display: inline-flex; align-items: center; gap: 10px; padding: 14px 34px; border-radius: 12px; font-weight: 700; color: white; background: linear-gradient(135deg, #e26d36 0%, #f97316 100%); border: 1px solid rgba(255,255,255,0.1); box-shadow: 0 10px 25px -5px rgba(226, 109, 54, 0.4); text-transform: uppercase; font-size: 0.82rem; letter-spacing: 0.05em; transition: var(--transition);">
                            <span><?= htmlspecialchars($page['hero_cta1_text']) ?></span>
                            <i data-lucide="arrow-right" style="width: 16px; height: 16px;"></i>
                        </a>
                    <?php endif; ?>

                    <?php if (!empty($page['hero_cta2_text'])): ?>
                        <a href="<?= htmlspecialchars(url($page['hero_cta2_url'] ?? '#services')) ?>" class="btn-cta-secondary" style="display: inline-flex; align-items: center; gap: 8px; padding: 14px 30px; border-radius: 12px; font-weight: 600; color: var(--text-main); background: rgba(255,255,255,0.05); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); border: 1px solid var(--border-md); box-shadow: none; transition: var(--transition);">
                            <span><?= htmlspecialchars($page['hero_cta2_text']) ?></span>
                        </a>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </section>

<?php elseif ($variant === 'hero_split_asymmetric'): ?>
    <!-- Variant 7: Asymmetric Stack Hero -->
    <section class="premium-hero hero-asymmetric-layout" id="hero-section-<?= $page['id'] ?>" style="<?= $bgStyle ?> position: relative; padding: <?= $heroPadding ?>; overflow: hidden; display: flex; <?= $verticalAlignCss ?> min-height: <?= $heroMinHeight ?>;">
        
        <div class="hero-glow-1" style="position: absolute; width: 600px; height: 600px; border-radius: 50%; background: radial-gradient(circle, rgba(124, 58, 237, 0.12) 0%, rgba(124, 58, 237, 0) 70%); top: -100px; right: -50px; z-index: 1; pointer-events: none;"></div>

        <div class="container" style="position: relative; z-index: 5; width: 100%;">
            <div class="hero-grid" style="display: grid; <?= $gridOrder ?> gap: 60px; align-items: center;">
                
                <!-- Text block -->
                <div class="hero-left-content hero-text-block" style="<?= $textOrder ?> max-width: <?= $heroTextWidth ?>; <?= $textMarginCss ?> <?= $textAlignmentCss ?> display: flex; flex-direction: column; <?= ($heroTextAlignment === 'center' || $heroTextAlignment === 'centre') ? 'align-items: center;' : (($heroTextAlignment === 'right' || $heroTextAlignment === 'droite') ? 'align-items: flex-end;' : 'align-items: flex-start;') ?> opacity: 0; transform: translateY(20px); animation: heroFadeIn 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;">
                    
                    <?php if (!empty($badge)): ?>
                        <div class="hero-brand-badge" style="display: inline-flex; align-items: center; gap: 8px; background: rgba(124, 58, 237, 0.08); border: 1px solid rgba(124, 58, 237, 0.2); padding: 6px 16px; border-radius: 50px; font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.15em; color: var(--secondary); margin-bottom: 24px; font-family: var(--font-heading);">
                            <div style="width: 6px; height: 6px; border-radius: 50%; background: var(--secondary); box-shadow: 0 0 8px var(--secondary);"></div>
                            <span><?= htmlspecialchars($badge) ?></span>
                        </div>
                    <?php endif; ?>

                    <h1 style="font-size: clamp(2.4rem, 5vw, 3.8rem); line-height: 1.15; font-weight: 900; letter-spacing: -0.03em; color: var(--text-main); margin-bottom: 20px; font-family: var(--font-heading);">
                        <?= $page['hero_title'] ?>
                    </h1>

                    <div style="display: flex; gap: 6px; margin: 20px 0 24px 0;">
                        <span style="width: 24px; height: 4px; border-radius: 2px; background: var(--primary);"></span>
                        <span style="width: 16px; height: 4px; border-radius: 2px; background: var(--secondary);"></span>
                    </div>

                    <?php if (!empty($page['hero_subtitle'])): ?>
                        <p style="font-size: 1.1rem; line-height: 1.7; color: var(--text-muted); margin-bottom: 36px; max-width: 580px; font-weight: 500;">
                            <?= htmlspecialchars($page['hero_subtitle']) ?>
                        </p>
                    <?php endif; ?>

                    <div style="display: flex; gap: 16px; flex-wrap: wrap; align-items: center;">
                        <?php if (!empty($page['hero_cta1_text'])): ?>
                            <a href="<?= htmlspecialchars(url($page['hero_cta1_url'] ?? '#contact')) ?>" class="btn-cta-primary" style="display: inline-flex; align-items: center; gap: 10px; padding: 14px 32px; border-radius: 12px; font-weight: 700; color: white; background: linear-gradient(135deg, #e26d36 0%, #f97316 100%); border: 1px solid rgba(255,255,255,0.1); box-shadow: 0 10px 25px -5px rgba(226, 109, 54, 0.4); text-transform: uppercase; font-size: 0.82rem; letter-spacing: 0.05em; transition: var(--transition);">
                                <span><?= htmlspecialchars($page['hero_cta1_text']) ?></span>
                                <i data-lucide="arrow-right" style="width: 16px; height: 16px;"></i>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($page['hero_cta2_text'])): ?>
                            <a href="<?= htmlspecialchars(url($page['hero_cta2_url'] ?? '#services')) ?>" class="btn-cta-secondary" style="display: inline-flex; align-items: center; gap: 8px; padding: 14px 28px; border-radius: 12px; font-weight: 600; color: var(--text-main); background: rgba(255,255,255,0.05); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); border: 1px solid var(--border-md); box-shadow: none; transition: var(--transition);">
                                <span><?= htmlspecialchars($page['hero_cta2_text']) ?></span>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Visual Stack column -->
                <?php
                $heroFeatures = [];
                if (!empty($page['hero_features'])) {
                    $heroFeatures = json_decode($page['hero_features'], true) ?: [];
                }
                if (empty($heroFeatures)) {
                    $heroFeatures = [
                        ['icon' => 'zap', 'title' => htmlspecialchars($settings['site_name'] ?? 'Digitalium'), 'desc' => 'Solutions digitales haute performance.', 'color' => 'rgba(79,70,229,0.1)', 'iconColor' => 'var(--primary)', 'offset' => '-15px'],
                        ['icon' => 'shield-check', 'title' => 'Sécurité & Fiabilité', 'desc' => 'Protection de vos données et transactions.', 'color' => 'rgba(124,58,237,0.1)', 'iconColor' => 'var(--secondary)', 'offset' => '0'],
                        ['icon' => 'layout-grid', 'title' => 'CMS Modulaire', 'desc' => 'Administration dynamique en temps réel.', 'color' => 'rgba(226,109,54,0.1)', 'iconColor' => '#e26d36', 'offset' => '15px'],
                    ];
                }
                $stackOffsets = ['-15px', '0', '15px'];
                ?>
                <div class="hero-right-visual" style="<?= $visualOrder ?> position: relative; opacity: 0; transform: scale(0.95) translateY(10px); animation: heroFadeIn 0.8s cubic-bezier(0.16, 1, 0.3, 1) 0.2s forwards; display: flex; justify-content: center; align-items: center;">
                    <div class="asymmetric-card-stack" style="display: flex; flex-direction: column; gap: 20px; width: 100%; max-width: 440px;">
                        <?php foreach (array_slice($heroFeatures, 0, 3) as $fi => $feat):
                            $offset = $stackOffsets[$fi] ?? '0';
                            $isBordered = $fi === 1;
                        ?>
                        <div class="glass-stack-card" style="background: rgba(255,255,255,<?= $isBordered ? '0.7' : '0.65' ?>); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); border: 1px solid rgba(255,255,255,<?= $isBordered ? '0.8' : '0.7' ?>); <?= $isBordered ? 'border-left: 4px solid var(--secondary);' : '' ?> border-radius: 16px; padding: 18px 24px; box-shadow: <?= $isBordered ? '0 20px 40px rgba(79,70,229,0.06)' : '0 10px 30px rgba(0,0,0,0.05)' ?>; display: flex; align-items: center; gap: 16px; transform: <?= $isBordered ? 'scale(1.03)' : "translateX($offset)" ?>; transition: all 0.4s ease-in-out;">
                            <div style="width: 42px; height: 42px; border-radius: 12px; background: <?= htmlspecialchars($feat['color'] ?? 'rgba(79,70,229,0.1)') ?>; display: flex; align-items: center; justify-content: center; color: <?= htmlspecialchars($feat['iconColor'] ?? 'var(--primary)') ?>;">
                                <i data-lucide="<?= htmlspecialchars($feat['icon'] ?? 'zap') ?>" style="width: 22px; height: 22px;"></i>
                            </div>
                            <div>
                                <h4 style="font-size: 0.95rem; font-weight: 700; color: var(--text-main); margin-bottom: 2px;"><?= htmlspecialchars($feat['title'] ?? '') ?></h4>
                                <p style="font-size: 0.75rem; color: var(--text-muted);"><?= htmlspecialchars($feat['desc'] ?? '') ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

            </div>
        </div>
    </section>

<?php elseif ($variant === 'hero_grid_features'): ?>
    <!-- Variant 8: Grid Features Hero -->
    <section class="premium-hero hero-grid-features-layout" id="hero-section-<?= $page['id'] ?>" style="<?= $bgStyle ?> position: relative; padding: <?= $heroPadding ?>; overflow: hidden; display: flex; <?= $verticalAlignCss ?> min-height: <?= $heroMinHeight ?>;">
        
        <div class="hero-glow-1" style="position: absolute; width: 600px; height: 600px; border-radius: 50%; background: radial-gradient(circle, rgba(99, 102, 241, 0.1) 0%, rgba(99, 102, 241, 0) 70%); top: -100px; right: -50px; z-index: 1; pointer-events: none;"></div>

        <div class="container" style="position: relative; z-index: 5; width: 100%;">
            <div class="hero-grid" style="display: grid; <?= $gridOrder ?> gap: 60px; align-items: center;">
                
                <!-- Text block -->
                <div class="hero-left-content hero-text-block" style="<?= $textOrder ?> max-width: <?= $heroTextWidth ?>; <?= $textMarginCss ?> <?= $textAlignmentCss ?> display: flex; flex-direction: column; <?= ($heroTextAlignment === 'center' || $heroTextAlignment === 'centre') ? 'align-items: center;' : (($heroTextAlignment === 'right' || $heroTextAlignment === 'droite') ? 'align-items: flex-end;' : 'align-items: flex-start;') ?> opacity: 0; transform: translateY(20px); animation: heroFadeIn 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;">
                    
                    <?php if (!empty($badge)): ?>
                        <div class="hero-brand-badge" style="display: inline-flex; align-items: center; gap: 8px; background: rgba(99, 102, 241, 0.08); border: 1px solid rgba(99, 102, 241, 0.2); padding: 6px 16px; border-radius: 50px; font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.15em; color: var(--primary); margin-bottom: 24px; font-family: var(--font-heading);">
                            <div style="width: 6px; height: 6px; border-radius: 50%; background: var(--primary); box-shadow: 0 0 8px var(--primary);"></div>
                            <span><?= htmlspecialchars($badge) ?></span>
                        </div>
                    <?php endif; ?>

                    <h1 style="font-size: clamp(2.4rem, 5vw, 3.8rem); line-height: 1.15; font-weight: 900; letter-spacing: -0.03em; color: var(--text-main); margin-bottom: 20px; font-family: var(--font-heading);">
                        <?= $page['hero_title'] ?>
                    </h1>

                    <div style="display: flex; gap: 6px; margin: 20px 0 24px 0;">
                        <span style="width: 24px; height: 4px; border-radius: 2px; background: var(--primary);"></span>
                        <span style="width: 16px; height: 4px; border-radius: 2px; background: var(--secondary);"></span>
                    </div>

                    <?php if (!empty($page['hero_subtitle'])): ?>
                        <p style="font-size: 1.1rem; line-height: 1.7; color: var(--text-muted); margin-bottom: 36px; max-width: 580px; font-weight: 500;">
                            <?= htmlspecialchars($page['hero_subtitle']) ?>
                        </p>
                    <?php endif; ?>

                    <div style="display: flex; gap: 16px; flex-wrap: wrap; align-items: center;">
                        <?php if (!empty($page['hero_cta1_text'])): ?>
                            <a href="<?= htmlspecialchars(url($page['hero_cta1_url'] ?? '#contact')) ?>" class="btn-cta-primary" style="display: inline-flex; align-items: center; gap: 10px; padding: 14px 32px; border-radius: 12px; font-weight: 700; color: white; background: linear-gradient(135deg, #e26d36 0%, #f97316 100%); border: 1px solid rgba(255,255,255,0.1); box-shadow: 0 10px 25px -5px rgba(226, 109, 54, 0.4); text-transform: uppercase; font-size: 0.82rem; letter-spacing: 0.05em; transition: var(--transition);">
                                <span><?= htmlspecialchars($page['hero_cta1_text']) ?></span>
                                <i data-lucide="arrow-right" style="width: 16px; height: 16px;"></i>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($page['hero_cta2_text'])): ?>
                            <a href="<?= htmlspecialchars(url($page['hero_cta2_url'] ?? '#services')) ?>" class="btn-cta-secondary" style="display: inline-flex; align-items: center; gap: 8px; padding: 14px 28px; border-radius: 12px; font-weight: 600; color: var(--text-main); background: rgba(255,255,255,0.05); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); border: 1px solid var(--border-md); box-shadow: none; transition: var(--transition);">
                                <span><?= htmlspecialchars($page['hero_cta2_text']) ?></span>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Visual Grid column -->
                <?php
                $gridFeats = [];
                if (!empty($page['hero_features'])) {
                    $gridFeats = json_decode($page['hero_features'], true) ?: [];
                }
                if (empty($gridFeats)) {
                    $gridFeats = [
                        ['icon' => 'cpu', 'title' => 'IA & Automation', 'desc' => 'Optimisez vos process métiers.', 'color' => 'rgba(79,70,229,0.1)', 'iconColor' => 'var(--primary)'],
                        ['icon' => 'cloud', 'title' => 'Infrastructure Cloud', 'desc' => 'Hébergement sécurisé scalable.', 'color' => 'rgba(124,58,237,0.1)', 'iconColor' => 'var(--secondary)'],
                        ['icon' => 'globe', 'title' => 'Applications Web', 'desc' => 'Solutions sur-mesure modernes.', 'color' => 'rgba(6,182,212,0.1)', 'iconColor' => '#0891b2'],
                        ['icon' => 'phone-call', 'title' => 'Support 24/7', 'desc' => 'Une équipe d\'experts à votre écoute.', 'color' => 'rgba(226,109,54,0.1)', 'iconColor' => '#e26d36'],
                    ];
                }
                ?>
                <div class="hero-right-visual" style="<?= $visualOrder ?> position: relative; opacity: 0; transform: scale(0.95) translateY(10px); animation: heroFadeIn 0.8s cubic-bezier(0.16, 1, 0.3, 1) 0.2s forwards; display: flex; justify-content: center; align-items: center;">
                    <div class="grid-features-wrap" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; width: 100%; max-width: 440px;">
                        <?php foreach (array_slice($gridFeats, 0, 4) as $gf): ?>
                        <div class="glass-feature-card" style="background: rgba(255,255,255,0.04); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.08); border-radius: 20px; padding: 24px 20px; box-shadow: 0 0 0 1px rgba(255,255,255,0.04), 0 10px 30px rgba(0,0,0,0.25); display: flex; flex-direction: column; gap: 14px; transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);">
                            <div style="width: 38px; height: 38px; border-radius: 10px; background: <?= htmlspecialchars($gf['color'] ?? 'rgba(79,70,229,0.1)') ?>; display: flex; align-items: center; justify-content: center; color: <?= htmlspecialchars($gf['iconColor'] ?? 'var(--primary)') ?>;">
                                <i data-lucide="<?= htmlspecialchars($gf['icon'] ?? 'zap') ?>" style="width: 20px; height: 20px;"></i>
                            </div>
                            <div>
                                <h4 style="font-size: 0.9rem; font-weight: 700; color: var(--text-main); margin-bottom: 4px;"><?= htmlspecialchars($gf['title'] ?? '') ?></h4>
                                <p style="font-size: 0.72rem; color: var(--text-muted); line-height: 1.4;"><?= htmlspecialchars($gf['desc'] ?? '') ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

            </div>
        </div>
    </section>

<?php else: 
    // Split layout (hero_split_large_image, hero_split_small_image, hero_floating_card)
    $visualMaxWidth = ($variant === 'hero_split_small_image') ? '380px' : '480px';
    $isFloating = ($variant === 'hero_floating_card' || $imageLayout === 'floating');
?>
    <!-- Variant 3 & 4: Split Layout Hero (Premium Overlap Mockup / Floating Card) -->
    <section class="premium-hero hero-split-layout" id="hero-section-<?= $page['id'] ?>" style="<?= $bgStyle ?> position: relative; padding: <?= $heroPadding ?>; overflow: hidden; display: flex; <?= $verticalAlignCss ?> min-height: <?= $heroMinHeight ?>;">
        
        <!-- Ambient Glows -->
        <div class="hero-glow-1" style="position: absolute; width: 500px; height: 500px; border-radius: 50%; background: radial-gradient(circle, rgba(124, 58, 237, 0.12) 0%, rgba(124, 58, 237, 0) 70%); top: -100px; right: -50px; z-index: 1; pointer-events: none;"></div>
        <div class="hero-glow-2" style="position: absolute; width: 500px; height: 500px; border-radius: 50%; background: radial-gradient(circle, rgba(30, 58, 138, 0.08) 0%, rgba(30, 58, 138, 0) 70%); bottom: -100px; left: -50px; z-index: 1; pointer-events: none;"></div>

        <div class="container" style="position: relative; z-index: 5; width: 100%;">
            <div class="hero-grid" style="display: grid; <?= $gridOrder ?> gap: 60px; align-items: center;">
                
                <!-- Hero Typography & CTAs -->
                <div class="hero-left-content hero-text-block" style="<?= $textOrder ?> max-width: <?= $heroTextWidth ?>; <?= $textMarginCss ?> <?= $textAlignmentCss ?> display: flex; flex-direction: column; <?= ($heroTextAlignment === 'center' || $heroTextAlignment === 'centre') ? 'align-items: center;' : (($heroTextAlignment === 'right' || $heroTextAlignment === 'droite') ? 'align-items: flex-end;' : 'align-items: flex-start;') ?> opacity: 0; transform: translateY(20px); animation: heroFadeIn 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;">
                    
                    <!-- Brand Badge / Label -->
                    <div class="hero-brand-badge" style="display: inline-flex; align-items: center; gap: 8px; background: rgba(124, 58, 237, 0.08); border: 1px solid rgba(124, 58, 237, 0.2); padding: 6px 16px; border-radius: 50px; font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.15em; color: var(--secondary); margin-bottom: 24px; font-family: var(--font-heading);">
                        <div style="width: 6px; height: 6px; border-radius: 50%; background: var(--secondary); box-shadow: 0 0 8px var(--secondary);"></div>
                        <span><?= htmlspecialchars(!empty($badge) ? $badge : ($settings['site_name'] ?? 'Digitalium Group')) ?></span>
                    </div>

                    <!-- Main Heading -->
                    <h1 style="font-size: clamp(2.4rem, 5vw, 3.8rem); line-height: 1.15; font-weight: 900; letter-spacing: -0.03em; color: var(--text-main); margin-bottom: 20px; font-family: var(--font-heading);">
                        <?= $page['hero_title'] ?>
                    </h1>

                    <!-- Colorful Accent Stripes -->
                    <div style="display: flex; gap: 6px; margin: 20px 0 24px 0;">
                        <span style="width: 24px; height: 4px; border-radius: 2px; background: var(--primary);"></span>
                        <span style="width: 16px; height: 4px; border-radius: 2px; background: var(--secondary);"></span>
                        <span style="width: 32px; height: 4px; border-radius: 2px; background: var(--accent);"></span>
                    </div>

                    <!-- Subtitle / Pitch -->
                    <?php if (!empty($page['hero_subtitle'])): ?>
                        <p style="font-size: 1.1rem; line-height: 1.7; color: var(--text-muted); margin-bottom: 36px; max-width: 580px; font-weight: 500;">
                            <?= htmlspecialchars($page['hero_subtitle']) ?>
                        </p>
                    <?php endif; ?>

                    <!-- Actions -->
                    <div style="display: flex; gap: 16px; flex-wrap: wrap; align-items: center;">
                        <?php if (!empty($page['hero_cta1_text'])): ?>
                            <a href="<?= htmlspecialchars($page['hero_cta1_url'] ?? '#contact') ?>" class="btn-cta-primary" style="display: inline-flex; align-items: center; gap: 10px; padding: 14px 32px; border-radius: 12px; font-weight: 700; color: white; background: linear-gradient(135deg, #e26d36 0%, #f97316 100%); border: 1px solid rgba(255,255,255,0.1); box-shadow: 0 10px 25px -5px rgba(226, 109, 54, 0.4); text-transform: uppercase; font-size: 0.82rem; letter-spacing: 0.05em; transition: var(--transition);">
                                <span><?= htmlspecialchars($page['hero_cta1_text']) ?></span>
                                <i data-lucide="arrow-right" style="width: 16px; height: 16px;"></i>
                            </a>
                        <?php endif; ?>

                        <?php if (!empty($page['hero_cta2_text'])): ?>
                            <a href="<?= htmlspecialchars($page['hero_cta2_url'] ?? '#services') ?>" class="btn-cta-secondary" style="display: inline-flex; align-items: center; gap: 8px; padding: 14px 28px; border-radius: 12px; font-weight: 600; color: var(--text-main); background: rgba(255,255,255,0.05); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); border: 1px solid var(--border-md); box-shadow: none; transition: var(--transition);">
                                <span><?= htmlspecialchars($page['hero_cta2_text']) ?></span>
                            </a>
                        <?php endif; ?>
                    </div>

                </div>

                <!-- Hero Visuel Column -->
                <div class="hero-right-visual" style="<?= $visualOrder ?> position: relative; opacity: 0; transform: scale(0.95) translateY(10px); animation: heroFadeIn 0.8s cubic-bezier(0.16, 1, 0.3, 1) 0.2s forwards; display: flex; justify-content: center; align-items: center;">
                    
                    <div class="visual-wrap" style="position: relative; <?= $sizeStyle ?> max-width: <?= $visualMaxWidth ?>; filter: drop-shadow(0 25px 50px rgba(30, 58, 138, 0.15));">
                        
                        <!-- Circular Expert Badge -->
                        <?php if ($variant !== 'hero_split_small_image'): ?>
                            <div class="expert-circular-badge" style="position: absolute; top: -35px; right: -25px; width: 110px; height: 110px; border-radius: 50%; background: linear-gradient(135deg, var(--primary) 0%, #1e40af 100%); border: 3px solid rgba(255,255,255,0.9); box-shadow: 0 15px 30px -5px rgba(30, 58, 138, 0.3); display: flex; flex-direction: column; align-items: center; justify-content: center; z-index: 10; color: white; text-align: center; padding: 8px; animation: floatBadge 6s ease-in-out infinite;">
                                <i data-lucide="award" style="width: 22px; height: 22px; margin-bottom: 4px; color: #f5b800;"></i>
                                <span style="font-size: 0.52rem; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 700; font-family: var(--font-heading); line-height: 1.1;">Digital Innovation Expert</span>
                            </div>
                        <?php endif; ?>

                        <!-- Layout variant rendering: floating card vs standard mockup laptop screen -->
                        <?php if ($isFloating): ?>
                            <!-- Floating Card Style with subtle neon borders -->
                            <div class="floating-glass-card" style="background: rgba(30, 41, 59, 0.45); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.12); padding: 16px; border-radius: 24px; box-shadow: 0 30px 60px rgba(0,0,0,0.3); animation: floatBadge 8s ease-in-out infinite; overflow: hidden; position: relative;">
                                <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(135deg, rgba(99, 102, 241, 0.08) 0%, transparent 100%); pointer-events: none;"></div>
                                <div class="card-screen" style="aspect-ratio: 1.4; border-radius: 16px; overflow: hidden; border: 1px solid rgba(255,255,255,0.15); position: relative;">
                                    <?php if (!empty($page['hero_image'])): ?>
                                        <picture>
                                            <?php if (!empty($heroImageMobile)): ?>
                                                <source srcset="<?= htmlspecialchars(url($heroImageMobile)) ?>" media="(max-width: 768px)">
                                            <?php endif; ?>
                                            <img src="<?= htmlspecialchars(url($page['hero_image'])) ?>" alt="En-tête" style="width: 100%; height: 100%; object-fit: cover; <?= $imageFilter ?>">
                                        </picture>
                                    <?php else: ?>
                                        <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #0b1329 0%, #1e1b4b 100%); display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 12px; color: white; padding: 20px; text-align: center;">
                                            <i data-lucide="cpu" style="width: 28px; height: 28px; color: var(--accent);"></i>
                                            <h4 style="color: white; font-size: 0.95rem; font-family: var(--font-heading);"><?= htmlspecialchars($page['title']) ?></h4>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- Standard Laptop Container Mockup -->
                            <div class="mockup-frame" style="background: rgba(11,18,34,0.8); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.08); padding: 12px; border-radius: 24px; box-shadow: 0 0 0 1px rgba(255,255,255,0.04), 0 25px 50px rgba(0,0,0,0.4);">
                                <div class="screen-frame" style="aspect-ratio: 1.5; background: #0f172a; border-radius: 16px; overflow: hidden; border: 2px solid #334155; position: relative;">
                                    <?php if (!empty($page['hero_image'])): ?>
                                        <picture>
                                            <?php if (!empty($heroImageMobile)): ?>
                                                <source srcset="<?= htmlspecialchars(url($heroImageMobile)) ?>" media="(max-width: 768px)">
                                            <?php endif; ?>
                                            <img src="<?= htmlspecialchars(url($page['hero_image'])) ?>" alt="En-tête" style="width: 100%; height: 100%; object-fit: cover; <?= $imageFilter ?>">
                                        </picture>
                                    <?php else: ?>
                                        <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #0b1329 0%, #1e1b4b 100%); display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 12px; color: white; padding: 20px; text-align: center;">
                                            <div style="width: 60px; height: 60px; border-radius: 50%; background: rgba(255,255,255,0.06); display: flex; align-items: center; justify-content: center; box-shadow: 0 0 20px rgba(99, 102, 241, 0.1);">
                                                <i data-lucide="cpu" style="width: 28px; height: 28px; color: var(--accent);"></i>
                                            </div>
                                            <h4 style="color: white; font-size: 1rem; font-family: var(--font-heading); font-weight: 600;"><?= htmlspecialchars($page['title']) ?></h4>
                                            <p style="font-size: 0.72rem; color: #94a3b8; max-width: 220px;">Digitalium Group &bull; Agence d'innovation et solutions sur-mesure</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>

            </div>
        </div>
    </section>
<?php endif; ?>

<style>
@keyframes heroFadeIn {
    to { opacity: 1; transform: translateY(0) scale(1); }
}
@keyframes floatBadge {
    0%, 100% { transform: translateY(0) rotate(0deg); }
    50% { transform: translateY(-8px) rotate(5deg); }
}
.btn-cta-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 15px 30px -5px rgba(226, 109, 54, 0.6) !important;
}
.btn-cta-secondary:hover {
    background: rgba(255,255,255,0.09) !important;
    border-color: rgba(99,102,241,0.4) !important;
    transform: translateY(-2px);
}
@media (max-width: 991px) {
    .hero-grid {
        grid-template-columns: 1fr !important;
        gap: 50px !important;
        text-align: center;
    }
    .hero-left-content {
        display: flex;
        flex-direction: column;
        align-items: center;
        order: 1 !important;
    }
    .hero-right-visual {
        order: 2 !important;
    }
    .hero-left-content div[style*="display: flex; gap: 6px"] {
        justify-content: center;
    }
    .expert-circular-badge {
        right: 10px !important;
    }
}
</style>
<?php endif; ?>
