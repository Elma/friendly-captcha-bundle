<?php

declare(strict_types=1);

namespace CORS\Bundle\FriendlyCaptchaBundle\Form\Type;

use CORS\Bundle\FriendlyCaptchaBundle\Validator\FriendlyCaptchaValid;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class FriendlyCaptchaType extends AbstractType
{
    protected string $sitekey;
    protected bool $useEuEndpoints;

    public function __construct(string $sitekey, bool $useEuEndpoints)
    {
        $this->sitekey = $sitekey;
        $this->useEuEndpoints = $useEuEndpoints;
    }

    public function getParent()
    {
        return HiddenType::class;
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $fcValues = array_filter([
            'start' => $options['start'] ?? null
        ]);

        if ($this->useEuEndpoints == true) {
            $fcValues['api-endpoint'] = 'eu';
        }

        $view->vars['sitekey'] = $this->sitekey;
        $view->vars['friendly_captcha'] = $fcValues;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'start' => 'focus',
            'constraints' => [new FriendlyCaptchaValid()]
        ]);

        $resolver->setAllowedValues('start', ['auto', 'focus', 'none']);
    }

    public function getBlockPrefix()
    {
        return 'cors_friendly_catcha_type';
    }

    public function getSiteKey(): string
    {
        return $this->sitekey;
    }

}
