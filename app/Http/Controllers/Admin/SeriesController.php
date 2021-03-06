<?php

namespace LaravelItalia\Http\Controllers\Admin;

use LaravelItalia\Domain\Series;
use LaravelItalia\Http\Controllers\Controller;
use LaravelItalia\Exceptions\NotSavedException;
use LaravelItalia\Exceptions\NotFoundException;
use LaravelItalia\Exceptions\NotDeletedException;
use LaravelItalia\Http\Requests\SeriesSaveRequest;
use LaravelItalia\Domain\Repositories\SeriesRepository;

/**
 * Class SeriesController
 * @package LaravelItalia\Http\Controllers\Admin
 */
class SeriesController extends Controller
{
    /**
     * SeriesController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:administrator');
    }

    /**
     * Mostra l'elenco delle serie presenti nel sistema.
     *
     * @param SeriesRepository $seriesRepository
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getIndex(SeriesRepository $seriesRepository)
    {
        $series = $seriesRepository->getAll();

        return view('admin.series_index', compact('series'));
    }

    /**
     * Mostra il form di aggiunta di una nuova serie.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getAdd()
    {
        return view('admin.series_add');
    }

    /**
     * Salva una nuova serie i cui dati sono passati in $request.
     *
     * @param SeriesSaveRequest $request
     * @param SeriesRepository $seriesRepository
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postAdd(SeriesSaveRequest $request, SeriesRepository $seriesRepository)
    {
        $series = Series::createFromTitleAndDescriptionAndMetaDescription(
            $request->get('title'),
            $request->get('description'),
            $request->get('metadescription')
        );

        try {
            $seriesRepository->save($series);
        } catch (NotSavedException $e) {
            return redirect('admin/series/add')
                ->withInput()
                ->with('error_message', 'Problemi in fase di salvataggio. Riprovare.');
        }

        return redirect('admin/series')->with('success_message', 'La serie è stata aggiunta correttamente.');
    }

    /**
     * Segna come pubblicata la serie di cui viene passato l'id.
     *
     * @param SeriesRepository $seriesRepository
     * @param $seriesId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function getPublish(SeriesRepository $seriesRepository, $seriesId)
    {
        try {
            /* @var $series Series */
            $series = $seriesRepository->findByid($seriesId);
        } catch (NotFoundException $e) {
            return redirect('admin/series')->with('error_message', 'La serie selezionata non è più disponibile.');
        }

        $series->is_published = true;

        try {
            $seriesRepository->save($series);
        } catch (NotSavedException $e) {
            return redirect('admin/series')->with('error_message', 'Errori in fase di modifica. Riprovare.');
        }

        return redirect('admin/series')->with('success_message', 'La serie è stata messa in pubblicazione correttamente.');
    }

    /**
     * Rimuove dalla pubblicazione la serie identificata da $seriesId.
     *
     * @param SeriesRepository $seriesRepository
     * @param $seriesId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function getUnpublish(SeriesRepository $seriesRepository, $seriesId)
    {
        try {
            /* @var $series Series */
            $series = $seriesRepository->findByid($seriesId);
        } catch (NotFoundException $e) {
            return redirect('admin/series')->with('error_message', 'La serie selezionata non è più disponibile.');
        }

        $series->is_published = false;

        try {
            $seriesRepository->save($series);
        } catch (NotSavedException $e) {
            return redirect('admin/series')->with('error_message', 'Errori in fase di modifica. Riprovare.');
        }

        return redirect('admin/series')->with('success_message', 'La serie è stata rimossa dalla pubblicazione correttamente.');
    }

    /**
     * Segna come completa la serie di cui viene passato l'id.
     *
     * @param SeriesRepository $seriesRepository
     * @param $seriesId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function getComplete(SeriesRepository $seriesRepository, $seriesId)
    {
        try {
            /* @var $series Series */
            $series = $seriesRepository->findByid($seriesId);
        } catch (NotFoundException $e) {
            return redirect('admin/series')->with('error_message', 'La serie selezionata non è più disponibile.');
        }

        $series->is_completed = true;

        try {
            $seriesRepository->save($series);
        } catch (NotSavedException $e) {
            return redirect('admin/series')->with('error_message', 'Errori in fase di modifica. Riprovare.');
        }

        return redirect('admin/series')->with('success_message', 'La serie è stata contrassegnata come completata.');
    }

    /**
     * Segna come incompleta la serie di cui viene passato l'id.
     *
     * @param SeriesRepository $seriesRepository
     * @param $seriesId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function getIncomplete(SeriesRepository $seriesRepository, $seriesId)
    {
        try {
            /* @var $series Series */
            $series = $seriesRepository->findByid($seriesId);
        } catch (NotFoundException $e) {
            return redirect('admin/series')->with('error_message', 'La serie selezionata non è più disponibile.');
        }

        $series->is_completed = false;

        try {
            $seriesRepository->save($series);
        } catch (NotSavedException $e) {
            return redirect('admin/series')->with('error_message', 'Errori in fase di modifica. Riprovare.');
        }

        return redirect('admin/series')->with('success_message', 'La serie è stata contrassegnata come non completata.');
    }

    /**
     * Mostra il form di modifica dei dettagli di una serie.
     *
     * @param SeriesRepository $seriesRepository
     * @param $seriesId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function getEdit(SeriesRepository $seriesRepository, $seriesId)
    {
        try {
            /* @var $series Series */
            $series = $seriesRepository->findByid($seriesId);
        } catch (NotFoundException $e) {
            return redirect('admin/series')->with('error_message', 'La serie selezionata non è più disponibile.');
        }

        return view('admin.series_edit', compact('series'));
    }

    /**
     * Salva le modifiche apportate ad una serie esistente.
     *
     * @param SeriesSaveRequest $request
     * @param SeriesRepository $seriesRepository
     * @param $seriesId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postEdit(SeriesSaveRequest $request, SeriesRepository $seriesRepository, $seriesId)
    {
        try {
            /* @var $series Series */
            $series = $seriesRepository->findByid($seriesId);
        } catch (NotFoundException $e) {
            return redirect('admin/series')->with('error_message', 'La serie selezionata non è più disponibile.');
        }

        $series->title = $request->get('title');
        $series->description = $request->get('description');
        $series->metadescription = $request->get('metadescription');

        try {
            $seriesRepository->save($series);
        } catch (NotSavedException $e) {
            return redirect('admin/series')->with('error_message', 'Errori in fase di modifica. Riprovare.');
        }

        return redirect('admin/series/edit/'.$seriesId)->with('success_message', 'Serie modificata correttamente.');
    }

    /**
     * Rimuove dal sistema la serie selezionata, di cui viene passato l'id.
     *
     * @param SeriesRepository $seriesRepository
     * @param $seriesId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function getDelete(SeriesRepository $seriesRepository, $seriesId)
    {
        try {
            /* @var $series Series */
            $series = $seriesRepository->findByid($seriesId);
        } catch (NotFoundException $e) {
            return redirect('admin/series')->with('error_message', 'La serie scelta è stata già rimossa.');
        }

        try {
            $seriesRepository->delete($series);
        } catch (NotDeletedException $e) {
            return redirect('admin/series')->with('error_message', 'Impossibile cancellare la serie scelta. Riprovare.');
        }

        return redirect('admin/series')->with('success_message', 'La serie è stata cancellata correttamente.');
    }
}
