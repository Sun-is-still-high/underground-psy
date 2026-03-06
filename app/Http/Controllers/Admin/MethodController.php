<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Method;
use Illuminate\Http\Request;

class MethodController extends Controller
{
    public function index()
    {
        $methods = Method::withCount('profiles')->orderBy('name')->get();
        return view('admin.methods.index', compact('methods'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => ['required', 'string', 'max:100', 'unique:methods,name']]);
        Method::create(['name' => $request->name]);
        return redirect()->route('admin.methods.index')->with('success', 'Метод добавлен.');
    }

    public function update(Request $request, Method $method)
    {
        $request->validate(['name' => ['required', 'string', 'max:100', 'unique:methods,name,' . $method->id]]);
        $method->update(['name' => $request->name]);
        return redirect()->route('admin.methods.index')->with('success', 'Метод обновлён.');
    }

    public function destroy(Method $method)
    {
        if ($method->profiles()->exists()) {
            return redirect()->route('admin.methods.index')
                ->with('error', "Метод «{$method->name}» используется психологами — удаление невозможно.");
        }
        $method->delete();
        return redirect()->route('admin.methods.index')->with('success', 'Метод удалён.');
    }
}
