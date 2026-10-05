<?php

namespace App\Tests;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class PasswordFormsTest extends CoreTest
{
    public function testRegistrationRejectsMismatchedPasswordsAndAcceptsValidOnes(): void
    {
        $client = static::createClient();
        $uniqueEmail = 'smoketest+' . uniqid() . '@example.com';

        $crawler = $client->request('GET', '/register');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Créer mon compte')->form();
        $form['registration[pseudo]'] = 'SmokeTestUser' . uniqid();
        $form['registration[email]'] = $uniqueEmail;
        $form['registration[plainPassword][first]'] = 'Abcdef1+';
        $form['registration[plainPassword][second]'] = 'Different1+';
        $client->submit($form);

        $this->assertSelectorTextContains('body', 'identiques');

        $crawler = $client->getCrawler();
        $form = $crawler->selectButton('Créer mon compte')->form();
        $form['registration[pseudo]'] = 'SmokeTestUser' . uniqid();
        $form['registration[email]'] = $uniqueEmail;
        $form['registration[plainPassword][first]'] = 'Abcdef1+';
        $form['registration[plainPassword][second]'] = 'Abcdef1+';

        $client->submit($form);

        $this->assertResponseRedirects('/login');

        $em = static::getContainer()->get(EntityManagerInterface::class);
        $created = $em->getRepository(User::class)->findOneBy(['email' => $uniqueEmail]);
        $this->assertNotNull($created, 'le compte doit avoir ete cree avec des mots de passe identiques');
    }

    public function testProfileRejectsMismatchedNewPasswords(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $user = $em->getRepository(User::class)->findOneBy(['email' => 'user@user.com']);
        $client->loginUser($user);

        $crawler = $client->request('GET', '/profile');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Enregistrer les modifications')->form();
        $form['profile[newPassword][first]'] = 'Abcdef1+';
        $form['profile[newPassword][second]'] = 'Different1+';
        $form['profile[currentPassword]'] = 'user';
        $client->submit($form);

        $this->assertSelectorTextContains('body', 'identiques');
    }
}
