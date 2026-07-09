@foreach ($sectionsOrder as $sectionKey)
    @switch($sectionKey)
        @case('hero')
            @if($hero['enabled'] ?? true)
            <section class="lp-hero" id="home">
                <div id="lp-particles"></div>
                <div class="lp-hero-glow lp-hero-glow--1"></div>
                <div class="lp-hero-glow lp-hero-glow--2"></div>
                <div class="lp-container">
                    <div class="lp-hero-grid">
                        <div data-aos="fade-right">
                            @if(!empty($hero['badge']))
                            <div class="lp-hero-badge"><i class="fa {{ $hero['badge_icon'] ?? 'fa-star' }}"></i> {{ $hero['badge'] }}</div>
                            @endif
                            <h1 class="lp-hero-title">{{ $hero['heading'] ?? 'Welcome to Al Rushd Online' }}</h1>
                            <p class="lp-hero-sub">{{ $hero['subheading'] ?? '' }}</p>
                            <p class="lp-hero-desc">{{ $hero['description'] ?? '' }}</p>
                            <div class="lp-hero-actions">
                                <a href="{{ $hero['primary_btn_url'] ?? '#forms' }}" class="lp-btn lp-btn--primary">{{ $hero['primary_btn_text'] ?? 'Apply Now' }}</a>
                                <a href="{{ $hero['secondary_btn_url'] ?? '#programs' }}" class="lp-btn lp-btn--outline">{{ $hero['secondary_btn_text'] ?? 'Explore Programs' }}</a>
                            </div>
                        </div>
                        <div class="lp-hero-visual" data-aos="fade-left" data-aos-delay="150">
                            <div class="lp-hero-shape lp-hero-shape--1"></div>
                            <div class="lp-hero-shape lp-hero-shape--2"></div>
                            @foreach(($hero['images'] ?? []) as $i => $img)
                            <img class="lp-hero-img lp-hero-img--{{ $i + 1 }}" src="{{ $img }}" alt="Al Rushd student" loading="{{ $i === 0 ? 'eager' : 'lazy' }}">
                            @endforeach
                            @foreach(($hero['float_cards'] ?? []) as $fi => $fc)
                            <div class="lp-hero-float-card lp-hero-float-card--{{ $fi + 1 }}">
                                <div class="lp-hero-float-icon"><i class="fa {{ $fc['icon'] ?? 'fa-star' }}"></i></div>
                                <div class="lp-hero-float-text">
                                    <strong>{{ $fc['title'] ?? '' }}</strong>
                                    <span>{{ $fc['subtitle'] ?? '' }}</span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>
            <div class="lp-wave" aria-hidden="true">
                <svg viewBox="0 0 1440 48" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill="#F9F7F0" d="M0,32 C360,64 720,0 1080,24 C1260,36 1380,40 1440,32 L1440,48 L0,48 Z"/>
                </svg>
            </div>
            @endif
            @break

        @case('statistics')
            @if($statsSec['enabled'] ?? true)
            <section class="lp-section lp-stats-wrap" id="trust">
                <div class="lp-container">
                    <div class="lp-stats-grid">
                        @foreach ($landing['stats'] as $i => $stat)
                        <div class="lp-stat-card" data-aos="fade-up" data-aos-delay="{{ $i * 80 }}">
                            <div class="lp-stat-value">
                                <span data-count="{{ $stat['value'] }}" data-suffix="{{ $stat['suffix'] }}">0{{ $stat['suffix'] }}</span>
                            </div>
                            <div class="lp-stat-label">{{ $stat['label'] }}</div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </section>
            @endif
            @break

        @case('about')
            @if($about['enabled'] ?? true)
            <section class="lp-section lp-section--cream" id="about">
                <div class="lp-container">
                    <div class="lp-about-grid">
                        <div class="lp-about-img-wrap" data-aos="fade-right">
                            <img src="{{ $about['image'] ?? asset('frontend/assets/img/open.jpg') }}" alt="Al Rushd Online School" loading="lazy">
                            @if(!empty($about['badge']))
                            <div class="lp-about-badge"><i class="fa fa-award"></i> {{ $about['badge'] }}</div>
                            @endif
                        </div>
                        <div data-aos="fade-left">
                            <span class="lp-eyebrow">{{ $about['eyebrow'] ?? 'About Us' }}</span>
                            <h2 class="lp-section-title">{{ $about['heading'] ?? 'About Al Rushd Online School' }}</h2>
                            @foreach(explode("\n\n", $about['description'] ?? '') as $para)
                                @if(trim($para))
                                <p class="lp-section-desc" style="text-align:left;margin:0 0 8px">{{ trim($para) }}</p>
                                @endif
                            @endforeach
                            <div class="lp-checklist">
                                @foreach (($about['features'] ?? []) as $item)
                                <div class="lp-check-item" data-aos="fade-left" data-aos-delay="{{ $loop->index * 80 }}">
                                    <span class="lp-check-icon"><i class="fa {{ $item['icon'] ?? 'fa-check' }}"></i></span>
                                    {{ $item['text'] ?? '' }}
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            @endif
            @break

        @case('features')
            @if($features['enabled'] ?? true)
            <section class="lp-section" id="features">
                <div class="lp-container">
                    <div class="lp-section-header" data-aos="fade-up">
                        <span class="lp-eyebrow">{{ $features['eyebrow'] ?? 'Why Choose Us' }}</span>
                        <h2 class="lp-section-title">{{ $features['heading'] ?? 'Excellence in Every Lesson' }}</h2>
                        <p class="lp-section-desc">{{ $features['description'] ?? '' }}</p>
                    </div>
                    <div class="lp-features-grid">
                        @foreach (($features['items'] ?? []) as $i => $feature)
                        <div class="lp-feature-card" data-aos="fade-up" data-aos-delay="{{ $i * 60 }}">
                            <div class="lp-feature-icon"><i class="fa {{ $feature['icon'] }}"></i></div>
                            <h3 class="lp-feature-title">{{ $feature['title'] }}</h3>
                            <p class="lp-feature-desc">{{ $feature['desc'] }}</p>
                        </div>
                        @endforeach
                    </div>
                </div>
            </section>
            @endif
            @break

        @case('forms_section')
            @if($formsSec['enabled'] ?? true)
            <section class="lp-section lp-section--cream" id="forms">
                <div class="lp-container">
                    <div class="lp-section-header" data-aos="fade-up">
                        <span class="lp-eyebrow">{{ $formsSec['eyebrow'] ?? 'Online Forms' }}</span>
                        <h2 class="lp-section-title">{{ $formsSec['heading'] ?? 'Available Forms' }}</h2>
                        <p class="lp-section-desc">{{ $formsSec['description'] ?? '' }}</p>
                    </div>
                    <div class="lp-forms-grid">
                        @foreach ($formCards as $i => $card)
                        <a href="{{ url($card['href']) }}" class="lp-form-card" data-aos="fade-up" data-aos-delay="{{ ($i % 3) * 80 }}">
                            <div class="lp-form-card-icon"><i class="fa {{ $card['icon'] }}"></i></div>
                            <h3 class="lp-form-card-title">{{ $card['label'] }}</h3>
                            <p class="lp-form-card-desc">{{ $card['description'] }}</p>
                            <span class="lp-form-card-arrow">Get started <i class="fa fa-arrow-right"></i></span>
                        </a>
                        @endforeach
                    </div>
                </div>
            </section>
            @endif
            @break

        @case('how_it_works')
            @if($hiw['enabled'] ?? true)
            <section class="lp-section" id="how-it-works">
                <div class="lp-container">
                    <div class="lp-section-header" data-aos="fade-up">
                        <span class="lp-eyebrow">{{ $hiw['eyebrow'] ?? 'Simple Process' }}</span>
                        <h2 class="lp-section-title">{{ $hiw['heading'] ?? 'How It Works' }}</h2>
                        <p class="lp-section-desc">{{ $hiw['description'] ?? '' }}</p>
                    </div>
                    <div class="lp-timeline" data-aos="fade-up">
                        @foreach (($hiw['steps'] ?? []) as $i => $step)
                        <div class="lp-timeline-step">
                            <div class="lp-timeline-num">{{ $i + 1 }}</div>
                            <div class="lp-timeline-label">{{ $step }}</div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </section>
            @endif
            @break

        @case('programs')
            @if($programsSec['enabled'] ?? true)
            <section class="lp-section lp-section--navy" id="programs">
                <div class="lp-container">
                    <div class="lp-section-header" data-aos="fade-up">
                        <span class="lp-eyebrow">{{ $programsSec['eyebrow'] ?? 'Our Programmes' }}</span>
                        <h2 class="lp-section-title">{{ $programsSec['heading'] ?? 'Education for Every Stage' }}</h2>
                        <p class="lp-section-desc">{{ $programsSec['description'] ?? '' }}</p>
                    </div>
                    <div class="lp-programs-grid">
                        @foreach (($programsSec['items'] ?? $landing['programs']) as $i => $program)
                        <div class="lp-program-card" data-aos="fade-up" data-aos-delay="{{ $i * 60 }}">
                            <div class="lp-program-icon"><i class="fa {{ $program['icon'] }}"></i></div>
                            <h3 class="lp-program-title">{{ $program['title'] }}</h3>
                            <p class="lp-program-desc">{{ $program['desc'] }}</p>
                        </div>
                        @endforeach
                    </div>
                </div>
            </section>
            @endif
            @break

        @case('gallery')
            @if($gallery['enabled'] ?? false)
            <section class="lp-section" id="gallery">
                <div class="lp-container">
                    <div class="lp-section-header" data-aos="fade-up">
                        <span class="lp-eyebrow">{{ $gallery['eyebrow'] ?? 'Gallery' }}</span>
                        <h2 class="lp-section-title">{{ $gallery['heading'] ?? 'Our Gallery' }}</h2>
                        <p class="lp-section-desc">{{ $gallery['description'] ?? '' }}</p>
                    </div>
                    <div class="lp-gallery-grid" data-aos="fade-up">
                        @foreach (($gallery['items'] ?? []) as $i => $item)
                        <figure class="lp-gallery-item" data-aos="fade-up" data-aos-delay="{{ ($i % 4) * 60 }}">
                            <img src="{{ $item['image'] ?? '' }}" alt="{{ $item['caption'] ?? 'Gallery image' }}" loading="lazy">
                            @if(!empty($item['caption']))
                            <figcaption>{{ $item['caption'] }}</figcaption>
                            @endif
                        </figure>
                        @endforeach
                    </div>
                </div>
            </section>
            @endif
            @break

        @case('testimonials')
            @if($testi['enabled'] ?? true)
            <section class="lp-section lp-section--cream" id="testimonials">
                <div class="lp-container">
                    <div class="lp-section-header" data-aos="fade-up">
                        <span class="lp-eyebrow">{{ $testi['eyebrow'] ?? 'Testimonials' }}</span>
                        <h2 class="lp-section-title">{{ $testi['heading'] ?? 'What Parents Say' }}</h2>
                        <p class="lp-section-desc">{{ $testi['description'] ?? '' }}</p>
                    </div>
                    <div class="lp-testimonials-wrap" data-aos="fade-up">
                        <button type="button" class="lp-swiper-btn lp-swiper-btn--prev" aria-label="Previous"><i class="fa fa-chevron-left"></i></button>
                        <div class="swiper lp-testimonials-swiper">
                            <div class="swiper-wrapper">
                                @foreach (($testi['items'] ?? $landing['testimonials']) as $testimonial)
                                <div class="swiper-slide">
                                    <div class="lp-testimonial-card">
                                        <div class="lp-testimonial-stars">
                                            @for ($s = 0; $s < ($testimonial['rating'] ?? 5); $s++)<i class="fa fa-star"></i>@endfor
                                        </div>
                                        <p class="lp-testimonial-text">"{{ $testimonial['text'] }}"</p>
                                        <div class="lp-testimonial-author">
                                            <div class="lp-testimonial-avatar">{{ strtoupper(substr($testimonial['name'], 0, 1)) }}</div>
                                            <div>
                                                <div class="lp-testimonial-name">{{ $testimonial['name'] }}</div>
                                                <div class="lp-testimonial-role">{{ $testimonial['role'] }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        <button type="button" class="lp-swiper-btn lp-swiper-btn--next" aria-label="Next"><i class="fa fa-chevron-right"></i></button>
                    </div>
                </div>
            </section>
            @endif
            @break

        @case('faq')
            @if($faqSec['enabled'] ?? true)
            <section class="lp-section" id="faq">
                <div class="lp-container">
                    <div class="lp-section-header" data-aos="fade-up">
                        <span class="lp-eyebrow">{{ $faqSec['eyebrow'] ?? 'FAQ' }}</span>
                        <h2 class="lp-section-title">{{ $faqSec['heading'] ?? 'Frequently Asked Questions' }}</h2>
                        <p class="lp-section-desc">{{ $faqSec['description'] ?? '' }}</p>
                    </div>
                    <div class="lp-faq-list" data-aos="fade-up">
                        @foreach (($faqSec['items'] ?? $landing['faq']) as $faq)
                        <div class="lp-faq-item">
                            <button type="button" class="lp-faq-question">
                                {{ $faq['q'] }}
                                <span class="lp-faq-icon"><i class="fa fa-plus"></i></span>
                            </button>
                            <div class="lp-faq-answer">
                                <p>{{ $faq['a'] }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </section>
            @endif
            @break

        @case('contact')
            @if($contactSec['enabled'] ?? true)
            <section class="lp-section lp-section--cream" id="contact">
                <div class="lp-container">
                    <div class="lp-section-header" data-aos="fade-up">
                        <span class="lp-eyebrow">{{ $contactSec['eyebrow'] ?? 'Get In Touch' }}</span>
                        <h2 class="lp-section-title">{{ $contactSec['heading'] ?? 'Contact Us' }}</h2>
                        <p class="lp-section-desc">{{ $contactSec['description'] ?? '' }}</p>
                    </div>
                    <div class="lp-contact-grid">
                        <div data-aos="fade-right">
                            <div class="lp-contact-info-item">
                                <div class="lp-contact-info-icon"><i class="fa fa-envelope"></i></div>
                                <div>
                                    <div class="lp-contact-info-label">Email</div>
                                    <div class="lp-contact-info-value">{{ $contactSec['email'] ?? $landing['contact']['email'] }}</div>
                                </div>
                            </div>
                            <div class="lp-contact-info-item">
                                <div class="lp-contact-info-icon"><i class="fa fa-phone"></i></div>
                                <div>
                                    <div class="lp-contact-info-label">Phone</div>
                                    <div class="lp-contact-info-value">{{ $contactSec['phone'] ?? $landing['contact']['phone'] }}</div>
                                </div>
                            </div>
                            <div class="lp-contact-info-item">
                                <div class="lp-contact-info-icon"><i class="fa fa-map-marker-alt"></i></div>
                                <div>
                                    <div class="lp-contact-info-label">Address</div>
                                    <div class="lp-contact-info-value">{{ $contactSec['address'] ?? $landing['contact']['address'] }}</div>
                                </div>
                            </div>
                            <div class="lp-contact-map">
                                <iframe src="{{ $contactSec['map_embed'] ?? $landing['contact']['map_embed'] }}" allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Al Rushd location"></iframe>
                            </div>
                        </div>
                        <form class="lp-contact-form" id="lpContactForm" action="{{ url('/enquire/store') }}" method="POST" data-aos="fade-left">
                            @csrf
                            <input type="hidden" name="subject" value="Landing Page Contact">
                            <div class="lp-form-group">
                                <label class="lp-form-label" for="contact_name">Full Name</label>
                                <input type="text" class="lp-form-input" id="contact_name" name="name" required placeholder="Your name">
                            </div>
                            <div class="lp-form-group">
                                <label class="lp-form-label" for="contact_email">Email Address</label>
                                <input type="email" class="lp-form-input" id="contact_email" name="email" required placeholder="you@email.com">
                            </div>
                            <div class="lp-form-group">
                                <label class="lp-form-label" for="contact_phone">Phone Number</label>
                                <input type="tel" class="lp-form-input" id="contact_phone" name="phone" placeholder="+44 ...">
                            </div>
                            <div class="lp-form-group">
                                <label class="lp-form-label" for="contact_message">Message</label>
                                <textarea class="lp-form-textarea" id="contact_message" name="message" required placeholder="How can we help you?"></textarea>
                            </div>
                            <button type="submit" class="lp-btn lp-btn--primary" style="width:100%">Send Message</button>
                        </form>
                    </div>
                </div>
            </section>
            @endif
            @break

        @case('cta')
            @if($cta['enabled'] ?? true)
            <section class="lp-cta" id="cta">
                <div class="lp-cta-glow"></div>
                <div class="lp-container" data-aos="zoom-in">
                    <h2 class="lp-cta-title">{{ $cta['heading'] ?? 'Ready to Join Al Rushd?' }}</h2>
                    <p class="lp-cta-desc">{{ $cta['description'] ?? '' }}</p>
                    <div class="lp-cta-actions">
                        <a href="{{ $cta['primary_url'] ?? '#forms' }}" class="lp-btn lp-btn--primary">{{ $cta['primary_text'] ?? 'Apply Now' }}</a>
                        <a href="{{ $cta['secondary_url'] ?? '#contact' }}" class="lp-btn lp-btn--outline">{{ $cta['secondary_text'] ?? 'Contact Us' }}</a>
                    </div>
                </div>
            </section>
            @endif
            @break
    @endswitch
@endforeach
