<?php

declare(strict_types=1);

namespace App\Modules\Pages\Support;

use App\Modules\Plugins\Support\BlockFieldDefinition;
use App\Modules\Plugins\Support\BlockTypeDefinition;

final class CoreBlockDefinitions
{
    /** @var list<string> */
    public const ALLOWED_TYPES = ['hero', 'rich_text', 'cta', 'contact_form', 'feature_grid', 'faq'];

    /**
     * @return list<BlockTypeDefinition>
     */
    public static function all(): array
    {
        return [
            new BlockTypeDefinition(
                type: 'hero',
                label: 'Hero',
                description: 'Headline section with optional subheadline',
                category: 'content',
                defaultProps: [
                    'headline' => 'Your headline here',
                    'subheadline' => 'Supporting line for your value proposition.',
                ],
                fields: [
                    new BlockFieldDefinition('headline', 'Headline', 'text'),
                    new BlockFieldDefinition('subheadline', 'Subheadline', 'text'),
                    new BlockFieldDefinition(
                        'image_uuid',
                        'Image media UUID',
                        'text',
                        '00000000-0000-0000-0000-000000000000',
                    ),
                ],
            ),
            new BlockTypeDefinition(
                type: 'rich_text',
                label: 'Rich text',
                description: 'Body copy paragraph',
                category: 'content',
                defaultProps: ['body' => 'Write your page content here.'],
                fields: [new BlockFieldDefinition('body', 'Body', 'textarea')],
            ),
            new BlockTypeDefinition(
                type: 'feature_grid',
                label: 'Feature grid',
                description: 'Heading with a list of feature items',
                category: 'content',
                defaultProps: [
                    'heading' => 'Why choose us',
                    'items' => [
                        ['title' => 'Fast', 'body' => 'Launch pages quickly with structured blocks.'],
                        ['title' => 'Flexible', 'body' => 'Compose layouts without losing control.'],
                    ],
                ],
                fields: [
                    new BlockFieldDefinition('heading', 'Heading', 'text'),
                    new BlockFieldDefinition('items', 'Features', 'item_list', null, [
                        new BlockFieldDefinition('title', 'Title', 'text'),
                        new BlockFieldDefinition('body', 'Body', 'textarea'),
                    ]),
                ],
            ),
            new BlockTypeDefinition(
                type: 'faq',
                label: 'FAQ',
                description: 'Heading with question and answer pairs',
                category: 'content',
                defaultProps: [
                    'heading' => 'Frequently asked questions',
                    'items' => [
                        [
                            'question' => 'What is Luma CMS?',
                            'answer' => 'A modular CMS with structured content and a visual page editor.',
                        ],
                    ],
                ],
                fields: [
                    new BlockFieldDefinition('heading', 'Heading', 'text'),
                    new BlockFieldDefinition('items', 'Questions', 'item_list', null, [
                        new BlockFieldDefinition('question', 'Question', 'text'),
                        new BlockFieldDefinition('answer', 'Answer', 'textarea'),
                    ]),
                ],
            ),
            new BlockTypeDefinition(
                type: 'cta',
                label: 'Call to action',
                description: 'Primary button link',
                category: 'actions',
                defaultProps: ['label' => 'Get started', 'url' => '/contact'],
                fields: [
                    new BlockFieldDefinition('label', 'Button label', 'text'),
                    new BlockFieldDefinition('url', 'URL', 'url'),
                ],
            ),
            new BlockTypeDefinition(
                type: 'contact_form',
                label: 'Contact form',
                description: 'Embeds a form by slug (e.g. contact)',
                category: 'forms',
                defaultProps: [
                    'form_slug' => 'contact',
                    'title' => 'Contact us',
                    'submit_label' => 'Send message',
                ],
                fields: [
                    new BlockFieldDefinition('form_slug', 'Form slug', 'text'),
                    new BlockFieldDefinition('title', 'Heading', 'text'),
                    new BlockFieldDefinition('submit_label', 'Submit button label', 'text'),
                ],
            ),
        ];
    }
}
