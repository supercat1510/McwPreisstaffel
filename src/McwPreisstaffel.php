<?php declare(strict_types=1);

namespace Mcw\Preisstaffel;

use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\ContainsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\System\CustomField\CustomFieldTypes;

class McwPreisstaffel extends Plugin
{

    public function install(InstallContext $installContext): void{

        $customFieldSetRepository = $this->container->get('custom_field_set.repository');
        $customFieldSetRepository->create($this->getCustomFieldSet(), $installContext->getContext());

    }

    public function uninstall(UninstallContext $uninstallContext): void{

        $this->deleteCustomFieldSet($uninstallContext->getContext());
        $this->deleteCustomFields($uninstallContext->getContext());

    }

    protected function deleteCustomFieldSet($context): void{

        $customFieldSetRepository = $this->container->get('custom_field_set.repository');

        $criteria = new Criteria();

        $criteria->addFilter(
            new EqualsFilter('name', 'mcwpreisstaffel')
        );

        $customFieldSetId = $customFieldSetRepository->searchIds($criteria, $context)->firstId();

        if (!empty($customFieldSetId)) {

            $customFieldSetRepository->delete(
                [
                    [
                        'id' => $customFieldSetId
                    ]
                ],
                $context
            );

        }

    }

    protected function deleteCustomFields($context): void{

        $customFieldRepository = $this->container->get('custom_field.repository');

        $criteria = new Criteria();

        $criteria->addFilter(
            new ContainsFilter('name', 'mcwpreisstaffel_product_abbis')
        );

        $searchResult = $customFieldRepository->searchIds($criteria, $context);

        $total = $searchResult->getTotal();

        if (!empty($total)) {

            $ids = $searchResult->getIds();

            if (!empty($ids)) {

                $data = [];

                foreach ($ids as $id) {

                    $data[] = [
                        'id' => $id
                    ];

                }

                $customFieldRepository->delete($data, $context);

            }

        }

    }

    protected function getCustomFieldSet(): array{

        return [
            [
                'name' => 'mcwpreisstaffel',
                'config' => [
                    'label' => [
                        'de-DE' => 'Zusatzinformationen',
                        'en-GB' => 'Additional informations',
                    ],
                ],
                'customFields' => [
                    [
                        'name' => 'mcwpreisstaffel_product_abbis_1',
                        'type' => CustomFieldTypes::BOOL,
                        'config' => [
                            'type' => 'switch',
                            'label' => [
                                'de-DE' => 'Ab-Preise',
                                'en-GB' => 'From-Prices',
                            ],
                            'helpText' => [
                                'de-DE' => 'Wenn aktiviert werden die Preise mit dem Zusatz "ab" angezeigt.',
                                'en-GB' => 'If activated the prices are shown with "from',
                            ],
                            'componentName' => 'sw-field',
                            'customFieldType' => 'switch',
                            'customFieldPosition' => 1,
                        ]
                    ]
                ],
                'relations' => [
                    [
                        'entityName' => $this->container->get(ProductDefinition::class)->getEntityName()
                    ]
                ]
            ]
        ];

    }

}
