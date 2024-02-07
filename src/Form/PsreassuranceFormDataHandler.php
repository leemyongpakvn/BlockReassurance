<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */
declare(strict_types=1);

namespace PrestaShop\Module\BlockReassurance\Form;

use Doctrine\ORM\EntityManagerInterface;
use PrestaShop\Module\BlockReassurance\Entity\Psreassurance;
use PrestaShop\Module\BlockReassurance\Entity\PsreassuranceLang;
use PrestaShop\Module\BlockReassurance\Repository\PsreassuranceRepository;
use PrestaShop\PrestaShop\Core\Form\IdentifiableObject\DataHandler\FormDataHandlerInterface;
use PrestaShopBundle\Entity\Repository\LangRepository;

class PsreassuranceFormDataHandler implements FormDataHandlerInterface
{
    /**
     * @var PsreassuranceRepository
     */
    private $psreassuranceRepository;

    /**
     * @var LangRepository
     */
    private $langRepository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @param PsreassuranceeRepository $psreassuranceRepository
     * @param LangRepository $langRepository
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        PsreassuranceRepository $psreassuranceRepository,
        LangRepository $langRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->psreassuranceRepository = $psreassuranceRepository;
        $this->langRepository = $langRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $data)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function update($id, array $data)
    {
        $psreassurance = $this->psreassuranceRepository->find($id);
        
        if ($psreassurance === null) {
            return 0;
        } else {
            foreach ($data as $key => $value) {
                switch ($key) {
                    case 'date_upd':
                        $psreassurance->setDateUpd($value);
                        break;
                    case 'position':
                        $psreassurance->setPosition($value);
                        break;
                    case 'status':
                        $psreassurance->setStatus($value);
                        break;
                }
            }
            
            $this->entityManager->persist($psreassurance);
            $this->entityManager->flush();
        
            //file_put_contents('tangiaba', $data['status'] . ' || ' . $psreassurance->getStatus());

            return 1;
        }
    }

    /**
     * @param Psreassurance $psreassurance
     * @param array $psr_languages
     * @param int $type_link
     * @param int $id_cms
     *
     * @todo migrate this temporary function to above standard function create
     */
    public function createLangs($psreassurance, $psr_languages, $type_link, $id_cms): void
    {
        foreach ($psr_languages as $langId => $langContent) {
            $lang = $this->langRepository->find($langId);
            $psreassuranceLang = new PsreassuranceLang();
            $psreassuranceLang
                ->setLang($lang)
                ->setTitle($langContent->title)
                ->setDescription($langContent->description)
                ->setLink($langContent->url)
            ;
            if (!empty($id_cms) && $type_link === Psreassurance::TYPE_LINK_CMS_PAGE) {
                $psreassurance->setCmsId($id_cms);
                $link = \Context::getContext()->link;
                $psreassuranceLang->setLink(
                    $link->getCMSLink($id_cms, null, null, $langId)
                );
            }
            $psreassurance->addPsreassuranceLang($psreassuranceLang);
        }

        if ($type_link == 'undefined') {
            $type_link = Psreassurance::TYPE_LINK_NONE;
        }
        $psreassurance->setLinkType($type_link);

        $this->entityManager->persist($psreassurance);
        $this->entityManager->flush();
    }

    /**
     * @param Psreassurance $psreassurance
     * @param array $psr_languages
     * @param int $type_link
     * @param int $id_cms
     *
     * @todo migrate this temporary function to above standard function update
     */
    public function updateLangs($psreassurance, $psr_languages, $type_link, $id_cms): void
    {
        foreach ($psr_languages as $langId => $langContent) {
            $lang = $this->langRepository->find($langId);
            $psreassuranceLang = $psreassurance->getPsreassuranceLangByLangId($langId);
            if (null === $psreassuranceLang) {
                continue;
            }
            $psreassuranceLang
                ->setTitle($langContent->title)
                ->setDescription($langContent->description)
                ->setLink($langContent->url)
            ;
            if (!empty($id_cms) && $type_link === Psreassurance::TYPE_LINK_CMS_PAGE) {
                $psreassurance->setCmsId($id_cms);
                $link = \Context::getContext()->link;
                $psreassuranceLang->setLink(
                    $link->getCMSLink($id_cms, null, null, $langId)
                );
            }
        }

        if ($type_link == 'undefined') {
            $type_link = Psreassurance::TYPE_LINK_NONE;
        }
        $psreassurance->setLinkType($type_link);

        $this->entityManager->persist($psreassurance);
        $this->entityManager->flush();
    }
}
