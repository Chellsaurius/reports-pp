<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Reportes
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    Subtotal: {{ number_format($subtotal,2) }}
                    <br>
                    Descuento: {{ number_format($descuento,2) }}
                    <br>
                    IVA: {{ number_format($iva,2) }}
                    <br>
                    Total: {{ number_format($total,2) }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>