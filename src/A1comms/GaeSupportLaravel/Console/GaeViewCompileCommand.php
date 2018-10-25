<?php

namespace A1comms\GaeSupportLaravel\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use A1comms\GaeSupportLaravel\View\FileViewFinder;
use A1comms\GaeSupportLaravel\View\ViewServiceProvider;
use A1comms\GaeSupportLaravel\View\Compilers\BladeCompiler;

/**
 * Deployment command for running on GAE.
 */
class GaeViewCompileCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'gae:viewcompile';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pre-Compile All Blade Views for Deployment';

    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * Manifest of generated files.
     *
     * @var array
     */
    protected $manifest = [];

    /**
     * Create a new view compiler command instance.
     *
     * @param  \Illuminate\Filesystem\Filesystem  $files
     * @return void
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->info("Blade Compiler: Startup...");

        $compiledDirectory = config('view.compiled', null);
        $viewPaths = config('view.paths', []);

        if (!$this->files->isDirectory($compiledDirectory)) {
            $this->files->makeDirectory($compiledDirectory, 0755, true);
        }

        $hints = app('view')->getFinder()->getHints();
        foreach( $hints as $namespace => $paths ) {
            $viewPaths = array_merge($paths, $viewPaths);
        }

        $this->info("Blade Compiler: Cleaning view storage directory (" . $compiledDirectory . ")...");
        $this->files->cleanDirectory($compiledDirectory);
        $this->info("Blade Compiler: Cleaning view storage directory...done");

        for ($i = 0; $i < count($viewPaths); $i++) {
            $path = $viewPaths[$i];
            $relativePath = FileViewFinder::getRelativePath(base_path(), $path);

            $this->info("Blade Compiler: Compiling views in " . $relativePath . " (" . ($i+1) . "/" . count($viewPaths) . ")...");

            $files = $this->files->allFiles($path);

            for ($g = 0; $g < count($files); $g++) {
                $file = $files[$g];
                $filePath = $file->getPathname();
                $fileRelativePath = FileViewFinder::getRelativePath(base_path(), $filePath);

                if (!preg_match("/(.*)\.blade\.php$/", $filePath)) {
                    $this->info("Blade Compiler: \tSkipping view (" . ($g+1) . "/" . count($files) . ") " . $fileRelativePath);
                    continue;
                }

                $compiler = new BladeCompiler($this->files, $compiledDirectory);
                $compiledPath = $compiler->compile($filePath);
                $this->manifest[$fileRelativePath] = FileViewFinder::getRelativePath($compiledDirectory, $compiledPath);

                $this->info("Blade Compiler: \tCompiled view (" . ($g+1) . "/" . count($files) . ") " . $fileRelativePath);
            }

            $this->info("Blade Compiler: Compiling views in " . $relativePath . " (" . ($i+1) . "/" . count($viewPaths) . ")...done");
        }

        $this->writeManifest($compiledDirectory);
    }

    public function writeManifest($compiledDirectory)
    {
        $this->files->put(
            $compiledDirectory . "/manifest.php", '<?php return '.var_export($this->manifest, true).';'.PHP_EOL
        );
    }
}