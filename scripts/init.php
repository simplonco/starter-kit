<?php
define('ROOT', realpath(__DIR__ . '/../'));

class CreateProject  {

    public $themeRepo = "https://github.com/Automattic/_s.git";
    public $themeDir;
    public $themeName;
    public $assetsDir;
    public $wpDir;
    public $config;

    public function __construct () {

        $this->config = require_once __DIR__ .'/../config.php';
        
        $this->assetsDir = ROOT . "/assets";
        $this->wpDir = ROOT . '/public';
        $this->themeDir = ROOT . "/assets/theme";
        $this->themeName = basename(ROOT);
        
        if (!is_dir($this->themeDir))  {
            $this->downloadWP();
            $this->cloneTheme();
            $this->replaceStrings();

            if(!$this->config['skip-symlink']) {
                $this->makeSymlink();
            }
        }
        
        if (!file_exists($this->wpDir . '/wp-config.php')) {
            $this->createConfig();
        }

        if (!$this->checkDB()) {
            $this->createDB();
        }

        if (!$this->isInstalled()) {
            $this->installWP();
        }
    }

    public function downloadWP () {
        chdir($this->wpDir);
        exec('wp core download --locale=fr_FR');
    }

    public function cloneTheme () {
        chdir($this->assetsDir);
        echo exec("git clone $this->themeRepo $this->themeDir");
        echo exec("rm -rf $this->themeDir/.git");
    }
    public function replaceStrings () {
        chdir($this->themeDir);
        echo exec('find . -type f | xargs perl -pi -e "s/\\b_s\\b/'. $this->themeName .'/g"');
    }

    public function makeSymlink () {
        chdir($this->wpDir . '/wp-content/themes/');
        symlink($this->themeDir, $this->themeName);
    }

    public function createConfig () {
        chdir($this->wpDir);
        $name = !empty($this->config['db']['name'])
            ? $this->config['db']['name']
            : $this->themeName;
        $creds = join('', [
            '--dbname=', $name,
            ' --dbuser=',  $this->config['db']['user'],
            ' --dbpass=',  $this->config['db']['pass'],
        ]);
        
        echo exec("wp config create $creds", $output, $status);
        
    }

    public function checkDB () {
        chdir($this->wpDir);
        exec("wp db check", $output, $status);
        return $status == 0;
    }

    public function isInstalled () {
        chdir($this->wpDir);
        exec("wp core is-installed", $output, $status);
        return $status == 0;
    }

    public function createDB () {
        chdir($this->wpDir);
        echo exec('wp db create');
    }

    public function installWP () {
        chdir($this->wpDir);
        
        $url = !empty($this->config['url'])
        ? $this->config['url'] 
        : $this->themeName . '.dev';
        $title = !empty($this->config['title'])
        ? $this->config['title']
        : $this->themeName;
        $params = join('', [
            '--url=', $url,
            ' --title=', $title,
            ' --admin_user=', 'admin',
            ' --admin_password=', 'admin',
            ' --admin_email=', 'admin@admin.com',
            ' --skip-email'
        ]);

        echo exec("wp core install $params");

        echo exec("wp theme activate $this->themeName");
        
    }

}

new CreateProject();