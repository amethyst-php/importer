<?php

namespace Railken\Amethyst\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Railken\Amethyst\Exceptions;
use Railken\Amethyst\Models\File;
use Railken\Amethyst\Models\Importer;
use Railken\Lem\Contracts\AgentContract;
use Railken\Template\Generators;
use Symfony\Component\Yaml\Yaml;

abstract class ImportCommonFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var \Railken\Amethyst\Models\Importer
     */
    protected $importer;

    /**
     * @var \Railken\Amethyst\Models\File
     */
    protected $file;

    /**
     * @var \Railken\Lem\Contracts\AgentContract|null
     */
    protected $agent;

    /**
     * Create a new job instance.
     *
     * @param \Railken\Amethyst\Models\Importer    $importer
     * @param \Railken\Amethyst\Models\File        $file
     * @param \Railken\Lem\Contracts\AgentContract $agent
     */
    public function __construct(Importer $importer, File $file, AgentContract $agent = null)
    {
        $this->importer = $importer;
        $this->file = $file;
        $this->agent = $agent;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $filePath = tempnam(sys_get_temp_dir(), 'amethyst');
        file_put_contents($filePath, file_get_contents($this->file->downloadable()));

        $reader = $this->getReader($filePath);

        $generator = new Generators\TextGenerator();

        $genFile = $generator->generateViewFile($this->importer->data);

        $importer = $this->importer;

        try {
            $this->read($reader, function ($index, $row) use ($generator, $genFile, $importer) {
                $results = Collection::make();

                $manager = $importer->data_builder->newInstanceData()->getManager();

                $data = Yaml::parse($generator->render($genFile, ['record' => $row]));

                if ($data === null) {
                    throw new Exceptions\ImportFormattingException(sprintf('Error while formatting row #%s', $index));
                }

                $resources = $importer->data_builder->newInstanceQuery($data)->get();

                if ($resources->count() > 0) {
                    foreach ($resources as $resource) {
                        $results[] = $manager->update($resource, $data);
                    }
                } else {
                    $results[] = $manager->create($data);
                }

                foreach ($results as $result) {
                    if (!$result->ok()) {
                        throw new Exceptions\ImportFailedException('Row(#'.$index.'): '.$result->getError(0)->getMessage());
                    }
                }
            });
        } catch (Exceptions\ImportFormattingException | \PDOException | \Railken\SQ\Exceptions\QuerySyntaxException | Exceptions\ImportFailedException $e) {
            unlink($filePath);

            return event(new \Railken\Amethyst\Events\ImportFailed($importer, $e, $this->agent));
        } catch (\Twig_Error $e) {
            $e = new \Exception($e->getRawMessage().' on line '.$e->getTemplateLine());

            unlink($filePath);

            return event(new \Railken\Amethyst\Events\ImportFailed($importer, $e, $this->agent));
        }

        unlink($filePath);
        event(new \Railken\Amethyst\Events\ImportSucceeded($importer, $this->agent));
    }
}
