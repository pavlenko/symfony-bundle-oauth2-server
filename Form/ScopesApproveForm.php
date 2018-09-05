<?php

namespace PE\Bundle\OAuth2ServerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class ScopesApproveForm extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('approve', SubmitType::class, [
            'label'              => 'approve_scopes.form.accept',
            'translation_domain' => 'PEOAuth2Server',
        ]);

        $builder->add('decline', SubmitType::class, [
            'label'              => 'approve_scopes.form.decline',
            'translation_domain' => 'PEOAuth2Server',
        ]);
    }
}