<?php
/**
 * Created by PhpStorm.
 * User: Mohamed
 * Date: 14/01/2017
 * Time: 14:57
 */

namespace AppBundle\Form;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;

class RegisterForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add("_username",TextType::class,array('label' => 'Votre Email','required'=>'true'))
            ->add("_password",PasswordType::class,array('label' => 'Mot de passe','required'=>"true"))
            ->add("_password_repeat",PasswordType::class,array('label' => 'Confirmer mot de passe','required'=>'true'));
    }
}