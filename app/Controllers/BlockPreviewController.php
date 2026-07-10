<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\BlockRenderer;
use CodeIgniter\HTTP\ResponseInterface;

class BlockPreviewController extends BasePublicWebController
{
    /**
     * Render a single block type dynamically using the actual frontend BlockRenderer.
     */
    public function preview(): ResponseInterface
    {
        $blockKeyRaw = $this->request->getPost('block_key');
        $configRaw   = $this->request->getPost('block_config');
        $dataRaw     = $this->request->getPost('block_data');
        $blockKey    = is_scalar($blockKeyRaw) ? (string) $blockKeyRaw : '';
        $configRaw   = is_scalar($configRaw) ? (string) $configRaw : '';
        $dataRaw     = is_scalar($dataRaw) ? (string) $dataRaw : '';

        $config = json_decode($configRaw ?: '{}', true) ?? [];
        $data   = json_decode($dataRaw ?: '{}', true) ?? [];

        // Build container block mock children if empty
        $children = $this->getMockChildren($blockKey);

        // Populate placeholders for block_data and block_config if empty
        $data = $this->getMockData($blockKey, $data, $config);

        $block = [
            'block_key'    => $blockKey,
            'block_config' => $config,
            'block_data'   => $data,
            'children'     => $children,
        ];

        $lang = service('request')->getLocale();

        $blockRenderer = new BlockRenderer();
        $html = $blockRenderer->render([$block], $lang);

        return $this->response
            ->setContentType('application/json')
            ->setJSON(['html' => $html]);
    }

    /**
     * Get mock placeholder data for simple blocks when they have empty fields.
     *
     * @param array<string, mixed> $data
     * @param array<string, mixed> $config
     * @return array<string, mixed>
     */
    private function getMockData(string $blockKey, array $data, array &$config = []): array
    {
        if ($blockKey === 'hero_banner') {
            if (empty($data['heading'])) {
                $data['heading'] = 'Previsualización de Banner';
            }
            if (empty($data['subheading'])) {
                $data['subheading'] = 'Este banner utiliza las tipografías y el diseño completo de tu sitio público.';
            }
            if (empty($data['image_url'])) {
                $data['image_url'] = 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1200&q=80';
            }
            if (empty($data['cta_label'])) {
                $data['cta_label'] = 'Acción Principal';
            }
            if (empty($data['cta_url'])) {
                $data['cta_url'] = '#';
            }
        }

        if ($blockKey === 'cta') {
            if (empty($data['heading'])) {
                $data['heading'] = '¿Listo para dar el siguiente paso?';
            }
            if (empty($data['text'])) {
                $data['text'] = 'Estamos aquí para ayudarte a transformar tus ideas en proyectos digitales exitosos.';
            }
            if (empty($data['label'])) {
                $data['label'] = 'Comenzar Ahora';
            }
            if (empty($data['url'])) {
                $data['url'] = '#';
            }
        }

        if ($blockKey === 'metrics_grid') {
            if (empty($data['heading'])) {
                $data['heading'] = 'Resultados que Hablan por Sí Mismos';
            }
        }

        if ($blockKey === 'social_links') {
            if (empty($data['heading'])) {
                $data['heading'] = 'Conéctate con Nosotros';
            }
            if (empty($data['links'])) {
                $data['links'] = [
                    ['platform' => 'facebook', 'url' => '#'],
                    ['platform' => 'twitter', 'url' => '#'],
                    ['platform' => 'instagram', 'url' => '#'],
                    ['platform' => 'linkedin', 'url' => '#']
                ];
            }
        }

        if ($blockKey === 'alert') {
            if (empty($data['title'])) {
                $data['title'] = 'Aviso Importante';
            }
            if (empty($data['message']) && empty($data['content'])) {
                $data['message'] = 'Este es un mensaje de alerta de ejemplo para mostrar cómo se ve el diseño en tu sitio público.';
            }
        }

        if ($blockKey === 'video_player') {
            if (empty($data['video_url'])) {
                $data['video_url'] = 'https://www.youtube.com/watch?v=dQw4w9WgXcQ';
            }
            if (empty($data['poster_url'])) {
                $data['poster_url'] = 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?auto=format&fit=crop&w=1200&q=80';
            }
            if (empty($data['heading'])) {
                $data['heading'] = 'Video de Presentación de Ejemplo';
            }
        }

        if ($blockKey === 'map_embed') {
            if (empty($config['embed_url'])) {
                $config['embed_url'] = 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3329.076891048822!2d-71.6186981!3d-33.0427771!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x9689e13b0a7dbf2d%3A0x600fb16cd72eb0f1!2sValparaiso%2C%20Chile!5e0!3m2!1sen!2scl!4v1680000000000!5m2!1sen!2scl';
            }
            if (empty($data['title'])) {
                $data['title'] = 'Nuestra Ubicación';
            }
            if (empty($data['caption'])) {
                $data['caption'] = 'Visítanos en nuestras oficinas centrales de Valparaíso, Chile.';
            }
        }

        if ($blockKey === 'rich_text') {
            if (empty($data['content'])) {
                $data['content'] = '<p>Este es un bloque de texto enriquecido de ejemplo. Puedes escribir párrafos, usar negritas, cursivas, listas y otros formatos directamente desde el editor de contenidos en el panel de administración.</p>';
            }
        }

        return $data;
    }

    /**
     * Get mock children for container blocks when they have none during preview.
     *
     * @return array<int, array<string, mixed>>
     */
    private function getMockChildren(string $blockKey): array
    {
        if ($blockKey === 'accordion') {
            return [
                [
                    'block_key' => 'accordion_item',
                    'block_config' => ['is_open' => true],
                    'block_data' => ['title' => '¿Cómo funciona la vista previa?', 'content' => '<p>La vista previa renderiza el componente real usando el motor de plantillas público.</p>']
                ],
                [
                    'block_key' => 'accordion_item',
                    'block_config' => ['is_open' => false],
                    'block_data' => ['title' => '¿Es fiel al diseño final?', 'content' => '<p>Sí, utiliza los mismos estilos CSS de Tailwind y tipografías que el sitio web público.</p>']
                ]
            ];
        }

        if ($blockKey === 'gallery') {
            return [
                [
                    'block_key' => 'gallery_item',
                    'block_config' => [],
                    'block_data' => ['title' => 'Imagen 1', 'image_url' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=600&q=80', 'alt_text' => 'Playa paradisíaca']
                ],
                [
                    'block_key' => 'gallery_item',
                    'block_config' => [],
                    'block_data' => ['title' => 'Imagen 2', 'image_url' => 'https://images.unsplash.com/photo-1470071459604-3b5ec3a7fe05?auto=format&fit=crop&w=600&q=80', 'alt_text' => 'Montañas brumosas']
                ],
                [
                    'block_key' => 'gallery_item',
                    'block_config' => [],
                    'block_data' => ['title' => 'Imagen 3', 'image_url' => 'https://images.unsplash.com/photo-1447752875215-b2761acb3c5d?auto=format&fit=crop&w=600&q=80', 'alt_text' => 'Sendero forestal']
                ]
            ];
        }

        if ($blockKey === 'tabs') {
            return [
                [
                    'block_key' => 'tab_item',
                    'block_config' => [],
                    'block_data' => ['title' => 'Pestaña de Ejemplo 1', 'content' => '<p>Este es el contenido de la primera pestaña de ejemplo.</p>']
                ],
                [
                    'block_key' => 'tab_item',
                    'block_config' => [],
                    'block_data' => ['title' => 'Pestaña de Ejemplo 2', 'content' => '<p>Este es el contenido de la segunda pestaña de ejemplo.</p>']
                ]
            ];
        }

        if ($blockKey === 'hero_slider') {
            return [
                [
                    'block_key' => 'slide_banner',
                    'block_config' => [],
                    'block_data' => [
                        'title' => 'Diapositiva de Banner 1',
                        'subtitle' => 'Subtítulo descriptivo para la primera diapositiva.',
                        'image_url' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1200&q=80',
                        'cta_label' => 'Ver Más',
                        'cta_url' => '#'
                    ]
                ],
                [
                    'block_key' => 'slide_banner',
                    'block_config' => [],
                    'block_data' => [
                        'title' => 'Diapositiva de Banner 2',
                        'subtitle' => 'Subtítulo descriptivo para la segunda diapositiva.',
                        'image_url' => 'https://images.unsplash.com/photo-1470071459604-3b5ec3a7fe05?auto=format&fit=crop&w=1200&q=80',
                        'cta_label' => 'Comenzar',
                        'cta_url' => '#'
                    ]
                ]
            ];
        }

        if ($blockKey === 'cards_slider') {
            return [
                [
                    'block_key' => 'slide_card',
                    'block_config' => [],
                    'block_data' => [
                        'title' => 'Tarjeta Deslizable 1',
                        'description' => 'Descripción breve para la tarjeta de ejemplo en el slider.',
                        'image_url' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=400&q=80',
                        'link_url' => '#'
                    ]
                ],
                [
                    'block_key' => 'slide_card',
                    'block_config' => [],
                    'block_data' => [
                        'title' => 'Tarjeta Deslizable 2',
                        'description' => 'Descripción breve para la tarjeta de ejemplo en el slider.',
                        'image_url' => 'https://images.unsplash.com/photo-1470071459604-3b5ec3a7fe05?auto=format&fit=crop&w=400&q=80',
                        'link_url' => '#'
                    ]
                ]
            ];
        }

        if ($blockKey === 'cards_grid') {
            return [
                [
                    'block_key' => 'card_item',
                    'block_config' => [],
                    'block_data' => [
                        'title' => 'Tarjeta de Grid 1',
                        'description' => 'Descripción corta de la primera tarjeta en la cuadrícula.',
                        'image_url' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=400&q=80',
                        'link_url' => '#'
                    ]
                ],
                [
                    'block_key' => 'card_item',
                    'block_config' => [],
                    'block_data' => [
                        'title' => 'Tarjeta de Grid 2',
                        'description' => 'Descripción corta de la segunda tarjeta en la cuadrícula.',
                        'image_url' => 'https://images.unsplash.com/photo-1470071459604-3b5ec3a7fe05?auto=format&fit=crop&w=400&q=80',
                        'link_url' => '#'
                    ]
                ]
            ];
        }

        if ($blockKey === 'metrics_grid') {
            return [
                [
                    'block_key' => 'metric_item',
                    'block_config' => [],
                    'block_data' => ['label' => 'Proyectos Completados', 'value' => '150+']
                ],
                [
                    'block_key' => 'metric_item',
                    'block_config' => [],
                    'block_data' => ['label' => 'Clientes Satisfechos', 'value' => '99%']
                ]
            ];
        }

        if ($blockKey === 'asset_showcase') {
            return [
                [
                    'block_key' => 'asset_item',
                    'block_config' => [],
                    'block_data' => [
                        'title' => 'Caso de Éxito PDF',
                        'url' => '#',
                        'category' => 'document'
                    ]
                ]
            ];
        }

        return [];
    }
}
