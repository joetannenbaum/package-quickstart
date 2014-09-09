<?php

if (empty($argv[1])) {
    die('gotta give me a project name');
}

new QuickStart($argv[1]);

class quickstart
{
    protected $project;

    protected $path;

    protected $src;

    protected $dir;

    public function __construct($project)
    {
        $this->project = $project;

        $this->dir     = strtolower(preg_replace('/(.)([A-Z])/', '$1-$2', $this->project));

        $this->path    = __DIR__ . '/../' . $this->dir;

        $this->src     = $this->path . '/src';

        $this->copyDir(__DIR__, $this->path);

        $this->fixReadMe();
        $this->fixFunctional();
        $this->addMainClass();
        $this->addMainTest();
        $this->removeGarbageFiles();
        $this->fixComposerFile();
        $this->doGulpSetup();
    }

    protected function copyDir($src, $dst)
    {
        $dir = opendir($src);
        @mkdir($dst);
        while (false !== ($file = readdir($dir))) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if (is_dir($src . '/' . $file)) {
                    $this->copyDir($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file,$dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }

    protected function replace($src, $dest = null)
    {
        $search = [
            '{{ $project_path }}' => $this->dir,
            '{{ $project }}'      => $this->project,
        ];

        $dest = $dest ?: $src;

        $content = file_get_contents($src);

        $content = str_replace(array_keys($search), array_values($search), $content);

        file_put_contents($dest, $content);
    }

    protected function fixComposerFile()
    {
        $content = $this->replace("{$this->path}/composer.json");

        exec("cd {$this->path} && composer install");
    }

    protected function fixReadMe()
    {
        $content = $this->replace("{$this->path}/README.md");
    }

    protected function fixFunctional()
    {
        $content = $this->replace("{$this->path}/functional/index.php");
    }

    protected function doGulpSetup()
    {
        exec("cd {$this->path} && npm install");
    }

    protected function addMainClass()
    {
        $content = $this->replace("{$this->src}/MainClass.php");

        rename("{$this->src}/MainClass.php", "{$this->src}/{$this->project}.php");
    }

    protected function addMainTest()
    {
        $content = $this->replace("{$this->path}/tests/TestBase.php");

        $content = $this->replace("{$this->path}/tests/MainTest.php");

        rename("{$this->path}/tests/MainTest.php", "{$this->path}/tests/{$this->project}Test.php");
    }

    protected function removeGarbageFiles()
    {
        unlink("{$this->path}/quickstart.php");
        exec("cd {$this->path} && rm -R .git");
    }

}
