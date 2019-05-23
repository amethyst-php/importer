<?php

namespace Railken\Amethyst\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Railken\Amethyst\Api\Http\Controllers\RestManagerController;
use Railken\Amethyst\Api\Http\Controllers\Traits as RestTraits;
use Railken\Amethyst\Managers\FileManager;
use Railken\Amethyst\Managers\ImporterManager;

class ImportersController extends RestManagerController
{
    use RestTraits\RestIndexTrait;
    use RestTraits\RestShowTrait;
    use RestTraits\RestCreateTrait;
    use RestTraits\RestUpdateTrait;
    use RestTraits\RestRemoveTrait;

    /**
     * The class of the manager.
     *
     * @var string
     */
    public $class = ImporterManager::class;

    /**
     * Import a file.
     *
     * @param int $id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function execute(int $id, Request $request)
    {
        $importer = $this->getManager()->getRepository()->findOneById($id);

        if (!$importer) {
            abort(404);
        }

        $fm = new FileManager($this->getManager()->getAgent());
        $file = $fm->getRepository()->findOneById($request->input('file_id'));

        if (!$file) {
            abort(404);
        }

        $result = $this->getManager()->import($importer, $file, $request->input('type'));

        return $result->ok() ? $this->success() : $this->error();
    }
}
