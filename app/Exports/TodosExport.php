<?php

namespace App\Exports;

use App\Models\Todo;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\ShouldAutoSize; // file excel langsung terbuka dengan lebar kolom yang pas dengan isinya
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;

class TodosExport implements FromQuery, WithHeadings, WithMapping, WithEvents, ShouldAutoSize
{
    // menyiapkan query database
    public function query(): Builder
    {
        $query = Todo::query();

        // menyaring judul
        if (request()->filled('title')) {
            $query->where('title', 'like', '%' . request('title') . '%');
        }

        // memecah daftar nama assignee yang dipisah koma lalu mencari yang cocok
        if (request()->filled('assignee')) {
            $assignees = array_map('trim', explode(',', request('assignee')));
            $query->whereIn('assignee', $assignees);
        }

        // menyaring batas awal/akhir tanggal tenggat waktu tugas
        if (request()->filled('start')) {
            $query->whereDate('due_date', '>=', request('start'));
        }

        if (request()->filled('end')) {
            $query->whereDate('due_date', '<=', request('end'));
        }

        // menyaring batas min/max durasi waktu pengerjaan
        if (request()->filled('min')) {
            $query->where('time_tracked', '>=', (float) request('min'));
        }

        if (request()->filled('max')) {
            $query->where('time_tracked', '<=', (float) request('max'));
        }

        // memecah daftar status yang dipisah koma lalu mencari yang sesuai
        if (request()->filled('status')) {
            $statuses = array_map('trim', explode(',', request('status')));
            $query->whereIn('status', $statuses);
        }

        // memecah daftar prioritas yang dipisah koma lalu mencari yang sesuai
        if (request()->filled('priority')) {
            $priorities = array_map('trim', explode(',', request('priority')));
            $query->whereIn('priority', $priorities);
        }

        return $query;
    }

    // nama judul kolom pada baris pertama di file excel
    public function headings(): array
    {
        return [
            'title',
            'assignee',
            'due_date',
            'time_tracked',
            'status',
            'priority',
        ];
    }

    // memilih dan memformat data tiap baris
    public function map($todo): array
    {
        return [
            $todo->title,
            $todo->assignee ?? '-',
            $todo->due_date->format('Y-m-d'),
            (float) $todo->time_tracked,
            $todo->status,
            $todo->priority,
        ];
    }

    // summary & styling
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // menghitung jumlah baris data dan total waktu
                $totalTodos = $this->query()->count();
                $totalTime  = (float) $this->query()->sum('time_tracked');
                $summaryRow = $totalTodos + 2; // menentukan posisi baris ringkasan

                $sheet->getStyle('A1:F1')->getFont()->setBold(true);                            // menebalkan header
                $sheet->getStyle("A{$summaryRow}:F{$summaryRow}")->getFont()->setBold(true);    // menebalkan summary

                // menuliskan nilai total tugas dan total waktu
                $sheet->setCellValue("A{$summaryRow}", 'Total Todos: ' . $totalTodos);
                $sheet->setCellValue("D{$summaryRow}", 'Total Time Tracked: ' . $totalTime);
            },
        ];
    }
}
