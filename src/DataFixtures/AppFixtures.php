<?php

namespace App\DataFixtures;

use App\Entity\Product;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Exception;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    /** @var int */
    const USERS_MAX_COUNT   = 50;
    const PRODUCT_MAX_COUNT = 200;

    /** @var Generator  */
    private Generator $_faker;

    /** @var ObjectManager */
    private ObjectManager $manager;

    /** @var array|string[]  */
    private array $_logsFormats = [
        // italic and blink may not work depending of your terminal
        'bold' => "\033[1m%s\033[0m",
        'dark' => "\033[2m%s\033[0m",
        'italic' => "\033[3m%s\033[0m",
        'underline' => "\033[4m%s\033[0m",
        'blink' => "\033[5m%s\033[0m",
        'reverse' => "\033[7m%s\033[0m",
        'concealed' => "\033[8m%s\033[0m",
        // foreground colors
        'black' => "\033[30m%s\033[0m",
        'red' => "\033[31m%s\033[0m",
        'green' => "\033[32m%s\033[0m",
        'yellow' => "\033[33m%s\033[0m",
        'blue' => "\033[34m%s\033[0m",
        'magenta' => "\033[35m%s\033[0m",
        'cyan' => "\033[36m%s\033[0m",
        'white' => "\033[37m%s\033[0m",
        // background colors
        'bg_black' => "\033[40m%s\033[0m",
        'bg_red' => "\033[41m%s\033[0m",
        'bg_green' => "\033[42m%s\033[0m",
        'bg_yellow' => "\033[43m%s\033[0m",
        'bg_blue' => "\033[44m%s\033[0m",
        'bg_magenta' => "\033[45m%s\033[0m",
        'bg_cyan' => "\033[46m%s\033[0m",
        'bg_white' => "\033[47m%s\033[0m",
    ];

    /**
     * Injections of require dependencies
     *
     * @param UserPasswordHasherInterface $passwordHasher
     */
    public function __construct(private readonly UserPasswordHasherInterface $passwordHasher)
    {

    }

    /**
     * Load fixtures
     *
     * @throws Exception
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        $this->_faker = Factory::create('fr_FR');

        // Creates entities
        $this->_createUsers();
        $this->_createAdminUsers();
        $this->_createProducts();

        // Flush
        $this->manager->flush();
    }

    /**
     * Create user
     *
     * @return void
     * @throws Exception
     */
    private function _createUsers(): void
    {
        $userCount = random_int(1, self::USERS_MAX_COUNT);
        $this->log("Create $userCount users");
        for ($i = 0; $i < $userCount; $i++) {
            $user = new User();
            $username = $this->_faker->userName;
            $user
                ->setUsername($username)
                ->setEmail($this->_faker->email)
                ->setPassword($this->passwordHasher->hashPassword($user, $this->_faker->password))
                ->setRoles(['ROLE_USER']);
            $this->log('[USER] ' . $username . ' was created', 'green');
            $this->manager->persist($user);
        }
    }

    /**
     * Create admin users
     *
     * @return void
     */
    private function _createAdminUsers(): void
    {
        $this->log("Create 2 admins users");
        $admin_user = new User();
        $admin_user
            ->setEmail('admin@bilemo.com')
            ->setPassword($this->passwordHasher->hashPassword($admin_user, 'admin'))
            ->setRoles(['ROLE_SUPER_ADMIN'])
            ->setUsername('admin');
        $this->manager->persist($admin_user);
        $this->log('[USER] admin was created', 'green');
        $classic_user = new User();
        $classic_user
            ->setEmail('user@bilemo.com')
            ->setPassword($this->passwordHasher->hashPassword($admin_user, 'user'))
            ->setRoles(['ROLE_USER'])
            ->setUsername('user');
        $this->manager->persist($classic_user);
        $this->log('[USER] user was created', 'green');
    }

    /**
     * Create figures
     *
     * @return void
     * @throws Exception
     */
    private function _createProducts(): void
    {
        $productCount = random_int(1, self::PRODUCT_MAX_COUNT);
        $this->log("Create $productCount products");
        for ($i = 0; $i < $productCount; $i++) {
            $product = new Product();
            $currentTime = new DateTimeImmutable();
            $randomImageLink = $this->retrieveRedirectUrl('https://picsum.photos/500/300');
            $productName = $this->_faker->name;
            $product
                ->setName($productName)
                ->setDescription($this->_faker->realText(2000))
                ->setImage($randomImageLink)
                ->setSku($this->_faker->uuid)
                ->setCreatedAt($currentTime);
            $this->log('[PRODUCT] ' . $productName . ' was created', 'green');
            $this->manager->persist($product);
        }
    }

    /**
     * Retrive redirect URL
     *
     * @param $url
     * @return mixed
     */
    private function retrieveRedirectUrl($url): mixed
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Must be set to true so that PHP follows any "Location:" header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $a = curl_exec($ch); // $a will contain all headers
        return curl_getinfo($ch, CURLINFO_EFFECTIVE_URL); // This is what you need, it will return you the last effective URL
    }

    /**
     * Log
     *
     * @param $msg
     * @param $format
     * @return void
     */
    private function log($msg, $format = null): void
    {
        if ($format) $msg = $this->setLogFormat($format, $msg);
        echo $msg . PHP_EOL;
    }

    /**
     * Set logs format
     *
     * @param $format
     * @param $msg
     * @return string
     */
    private function setLogFormat($format, $msg): string
    {
        return sprintf($this->_logsFormats[$format], $msg);
    }
}
