@extends('layouts.admin')
@section('title', 'Gestão de Produtos – Admin Dona Sogra')

@section('content')
        
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl mb-6 font-bold">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow-md overflow-hidden">
            <div class="px-6 py-4 bg-brand-dark text-white flex justify-between items-center">
                <h1 class="font-black text-xl">🥐 Gerenciamento de Produtos & Estoque</h1>
                <span class="text-xs text-brand-gold font-bold">{{ $produtos->count() }} Salgados Cadastrados</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200 text-gray-500 uppercase text-xs">
                            <th class="py-3 px-6">Salgado</th>
                            <th class="py-3 px-6">Preço Unitário</th>
                            <th class="py-3 px-6">Estoque Atual</th>
                            <th class="py-3 px-6">Status</th>
                            <th class="py-3 px-6 text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($produtos as $produto)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="py-3 px-6 font-bold flex items-center gap-3">
                                    <img src="{{ asset($produto->imagem) }}" alt="" class="w-10 h-10 object-cover rounded-lg">
                                    <div>
                                        <p class="text-brand-dark font-black">{{ $produto->nome }}</p>
                                        <p class="text-gray-400 text-xs font-normal">{{ $produto->slug }}</p>
                                    </div>
                                </td>
                                <form action="{{ url('/admin/produtos/' . $produto->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <td class="py-3 px-6">
                                        <div class="flex items-center gap-1">
                                            <span class="text-gray-400">R$</span>
                                            <input type="number" step="0.01" name="preco_unitario" value="{{ $produto->preco_unitario }}" class="w-20 border rounded px-2 py-1 font-bold text-brand-red">
                                        </div>
                                    </td>
                                    <td class="py-3 px-6">
                                        <input type="number" name="estoque_atual" value="{{ $produto->estoque_atual }}" class="w-24 border rounded px-2 py-1 font-bold text-center">
                                        <span class="text-xs text-gray-400">un.</span>
                                    </td>
                                    <td class="py-3 px-6">
                                        <label class="flex items-center gap-2 cursor-pointer text-xs font-bold">
                                            <input type="checkbox" name="ativo" {{ $produto->ativo ? 'checked' : '' }} class="rounded text-brand-red">
                                            <span class="{{ $produto->ativo ? 'text-green-600' : 'text-red-500' }}">{{ $produto->ativo ? 'Ativo' : 'Inativo' }}</span>
                                        </label>
                                    </td>
                                    <td class="py-3 px-6 text-center">
                                        <button type="submit" class="bg-brand-red text-white text-xs font-bold px-4 py-2 rounded-lg hover:bg-red-700 transition shadow">
                                            Salvar
                                        </button>
                                    </td>
                                </form>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

@endsection
