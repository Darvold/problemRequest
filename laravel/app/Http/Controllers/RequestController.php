<?php

namespace App\Http\Controllers;

use App\Models\Request;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Auth;

class RequestController extends Controller
{
    /**
     * Показать форму создания заявки
     */
    public function create()
    {
        // Только диспетчер может создавать заявки
        if (Auth::user()->role !== 'dispatcher') {
            abort(403, 'Только диспетчер может создавать заявки');
        }

        return view('requests.create');
    }

    /**
     * Сохранить новую заявку
     */
    public function store(HttpRequest $request)
    {
        // Только диспетчер может создавать заявки
        if (Auth::user()->role !== 'dispatcher') {
            abort(403, 'Только диспетчер может создавать заявки');
        }

        $validated = $request->validate([
            'clientName' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:255',
            'problemText' => 'required|string',
        ]);

        Request::create([
            'clientName' => $validated['clientName'],
            'phone' => $validated['phone'],
            'address' => $validated['address'],
            'problemText' => $validated['problemText'],
            'status' => 'new', // Статус new по умолчанию
            'assignedTo' => null,
        ]);

        return redirect()->route('dispatcher.dashboard')
            ->with('success', 'Заявка успешно создана');
    }

    /**
     * Просмотр деталей заявки
     */
    public function show($id)
    {
        $request = Request::with('master')->findOrFail($id);
        
        // Проверка прав доступа
        $user = Auth::user();
        if ($user->role === 'master' && $request->assignedTo !== $user->id) {
            abort(403, 'У вас нет доступа к этой заявке');
        }

        return view('requests.show', compact('request'));
    }
}