<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BuilderSection extends Model
{
    use \App\Traits\BelongsToStore;

    protected $fillable = ['store_id', 'page_id', 'type', 'position', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function page(): BelongsTo
    {
        return $this->belongsTo(BuilderPage::class, 'page_id');
    }

    public function contents(): HasMany
    {
        return $this->hasMany(BuilderContent::class, 'section_id');
    }

    public function getContent(string $key, $default = ''): string
    {
        $content = $this->contents->firstWhere('key', $key);
        return $content ? $content->value : $default;
    }

    public static function sectionTypes(): array
    {
        return [
            'hero' => ['label' => 'Hero Section', 'icon' => 'sparkles', 'description' => 'Full-width hero with headline, subtitle, and CTA button'],
            'product_grid' => ['label' => 'Product Grid', 'icon' => 'squares-2x2', 'description' => 'Display featured products in a grid layout'],
            'banner' => ['label' => 'Banner', 'icon' => 'photo', 'description' => 'Full-width image banner with overlay text'],
            'text_block' => ['label' => 'Text Block', 'icon' => 'document-text', 'description' => 'Rich text content section'],
            'cta' => ['label' => 'Call to Action', 'icon' => 'cursor-arrow-rays', 'description' => 'Conversion-focused section with prominent CTA'],
            'features' => ['label' => 'Features Grid', 'icon' => 'check-badge', 'description' => 'Highlight your core offerings or benefits'],
            'testimonials' => ['label' => 'Testimonials', 'icon' => 'chat-bubble-bottom-center-text', 'description' => 'Build trust with customer reviews'],
            'faq' => ['label' => 'FAQ Accordion', 'icon' => 'question-mark-circle', 'description' => 'Answer common customer questions directly'],
            'newsletter' => ['label' => 'Newsletter Signup', 'icon' => 'envelope', 'description' => 'Capture customer leads and build your mailing list'],
            'contact' => ['label' => 'Contact Form', 'icon' => 'phone', 'description' => 'Allow customers to reach out to you directly'],
            'custom_code' => ['label' => 'Custom Code', 'category' => 'logic', 'icon' => 'code-bracket', 'description' => 'Inject custom HTML, CSS, and JS blocks'],
        ];
    }

    public static function defaultContent(string $type): array
    {
        return match($type) {
            'hero' => [
                'title' => 'Welcome to Our Store',
                'subtitle' => 'Discover amazing products at great prices',
                'button_text' => 'Shop Now',
                'button_url' => '#products',
                'image' => '',
                'overlay_color' => 'rgba(0,0,0,0.4)',
            ],
            'product_grid' => [
                'title' => 'Featured Products',
                'subtitle' => 'Handpicked just for you',
                'count' => '8',
            ],
            'banner' => [
                'title' => 'Special Offer',
                'subtitle' => 'Up to 50% off on selected items',
                'image' => '',
                'button_text' => 'View Deals',
                'button_url' => '#',
            ],
            'text_block' => [
                'title' => 'About Us',
                'content' => 'We are passionate about delivering the best products and service to our customers.',
            ],
            'cta' => [
                'title' => 'Ready to get started?',
                'subtitle' => 'Join thousands of happy customers',
                'button_text' => 'Get Started',
                'button_url' => '#',
            ],
            'features' => [
                'title' => 'Why Choose Us',
                'subtitle' => 'What makes us the best choice for you.',
                'feature_1_title' => 'Free Shipping',
                'feature_1_desc' => 'On all orders securely delivered',
                'feature_2_title' => '24/7 Support',
                'feature_2_desc' => 'Dedicated customer service at any time',
                'feature_3_title' => 'Secure Payments',
                'feature_3_desc' => 'Industry leading encryption for your peace of mind',
            ],
            'testimonials' => [
                'title' => 'What Our Customers Say',
                'review_1' => 'Absolutely love the quality of these products! Arrived super fast.',
                'author_1' => 'Sarah Jenkins',
                'review_2' => 'Best purchase I have made all year. Highly recommended.',
                'author_2' => 'Michael Chen',
            ],
            'faq' => [
                'title' => 'Frequently Asked Questions',
                'q1' => 'Do you ship internationally?',
                'a1' => 'Yes! We ship strictly to 125 different countries worldwide.',
                'q2' => 'What is your return policy?',
                'a2' => 'We offer a hassle-free 30 day return policy for unused items.',
            ],
            'newsletter' => [
                'title' => 'Join Our Newsletter',
                'subtitle' => 'Get the latest updates and exclusive offers directly in your inbox.',
                'button_text' => 'Subscribe',
                'placeholder' => 'Enter your email address',
                'success_message' => 'Thank you for subscribing!',
            ],
            'contact' => [
                'title' => 'Get in Touch',
                'subtitle' => 'Have questions? We are here to help. Send us a message and we will get back to you soon.',
                'name_label' => 'Name',
                'email_label' => 'Email',
                'subject_label' => 'Subject',
                'message_label' => 'Message',
                'button_text' => 'Send Message',
                'success_message' => 'Your message has been sent successfully!',
            ],
            'custom_code' => [
                'html' => '<!-- Add your HTML here -->',
                'css' => '/* Add your CSS here */',
                'js' => '// Add your JS here',
            ],
            default => [],
        };
    }
}
