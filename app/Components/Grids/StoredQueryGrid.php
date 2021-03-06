<?php

namespace FKSDB\Components\Grids;

use Exports\ExportFormatFactory;
use Exports\StoredQuery;
use FKSDB\Components\Controls\StoredQueryComponent;
use PDOException;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class StoredQueryGrid extends BaseGrid {

    /**
     * @var StoredQuery
     */
    private $storedQuery;

    /**
     * @var ExportFormatFactory
     */
    private $exportFormatFactory;

    /**
     * StoredQueryGrid constructor.
     * @param StoredQuery $storedQuery
     * @param ExportFormatFactory $exportFormatFactory
     */
    function __construct(StoredQuery $storedQuery, ExportFormatFactory $exportFormatFactory) {
        parent::__construct();
        $this->storedQuery = $storedQuery;
        $this->exportFormatFactory = $exportFormatFactory;
    }

    /**
     * @param $presenter
     * @throws \Nette\Application\UI\InvalidLinkException
     * @throws \NiftyGrid\DuplicateColumnException
     * @throws \NiftyGrid\DuplicateGlobalButtonException
     */
    protected function configure($presenter) {
        parent::configure($presenter);
        //
        // data
        //
        $this->setDataSource($this->storedQuery);

        //
        // columns
        //
        try {
            $c = 0;
            foreach ($this->storedQuery->getColumnNames() as $name) {
                $this->addColumn($c + 1, $name)->setRenderer(function($row) use($c) {
                            echo $row[$c];
                        });
                ++$c;
            }
        } catch (PDOException $e) {
            // pass, exception should be handled inn parent components
        }

        //
        // operations
        //
        $this->paginate = false;

        foreach ($this->exportFormatFactory->getFormats($this->storedQuery) as $formatName => $label) {
            $this->addGlobalButton('format_' . $formatName)
                    ->setLabel($label)
                    ->setLink($this->getParent()->link('format!', array('format' => $formatName)));
        }


        if (!$this->storedQuery->getQueryPattern()->isNew()) {
            $this->addGlobalButton('show')
                    ->setLabel(_('Podrobnosti dotazu'))
                    ->setClass('btn btn-sm btn-secondary')
                    ->setLink($this->getPresenter()->link('Export:show', $this->storedQuery->getQueryPattern()->getPrimary()));
            if ($qid = $this->storedQuery->getQueryPattern()->qid) { // intentionally =
                $parameters = array('qid' => $qid, 'bc' => null);
                $queryParameters = $this->storedQuery->getParameters();
                foreach ($this->storedQuery->getParameterNames() as $key) {
                    if (array_key_exists($key, $queryParameters)) {
                        $parameters[StoredQueryComponent::PARAMETER_URL_PREFIX . $key] = $queryParameters[$key];
                    }
                }
                $this->addGlobalButton('qid')
                        ->setLabel(_('Odkaz'))
                        ->setClass('btn btn-sm btn-secondary')
                        ->setLink($this->getPresenter()->link('Export:execute', $parameters));
            }
        }
    }

}
