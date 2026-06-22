<?php

namespace App\Controllers;

use App\Services\TatkalBookingService;

class TatkalController extends BaseController
{
    private TatkalBookingService $service;

    public function __construct()
    {
        $this->service = new TatkalBookingService();
    }

    public function dashboard()
    {
        $term = trim((string) $this->request->getGet('q'));

        return view('tatkal/dashboard', [
            'dashboard' => $this->service->dashboard(),
            'term' => $term,
            'results' => $term !== '' ? $this->service->search($term) : [],
        ]);
    }

    public function pnr()
    {
        $pnr = trim((string) $this->request->getGet('pnr'));

        return view('tatkal/pnr', [
            'pnr' => $pnr,
            'booking' => $pnr !== '' ? $this->service->pnr($pnr) : null,
        ]);
    }

    public function cancel()
    {
        $pnr = trim((string) $this->request->getPost('pnr'));
        $reason = trim((string) $this->request->getPost('reason')) ?: 'Admin cancellation';
        $result = $this->service->cancel($pnr, $reason);

        return redirect()->to(site_url('tatkal/pnr?pnr=' . urlencode($pnr)))
            ->with($result['ok'] ? 'message' : 'error', $result['message'] ?? 'Cancellation processed.');
    }

    public function rejected()
    {
        return view('tatkal/rejected', [
            'requests' => $this->service->rejectedRequests(),
        ]);
    }

    public function reset()
    {
        $this->service->reset();

        return redirect()->to(site_url('tatkal'))
            ->with('message', 'Booking history and rejected request history reset successfully.');
    }

    public function liveMetrics()
    {
        return $this->response->setJSON($this->service->dashboard()['metrics'] ?? []);
    }
}
