<style>
    .relative {
        position: relative;
    }

    .table {
        display: table;
    }

    .w-full {
        width: 100%;
    }

    .overflow-x-auto {
        overflow-x: auto;
    }

    .whitespace-nowrap {
        white-space: nowrap;
    }

    .border-b {
        border-bottom-width: 1px;
    }

    .border-gray-700 {
        --tw-border-opacity: 1;
        border-color: rgb(55 65 81 / var(--tw-border-opacity));
    }

    .bg-gray-50 {
        --tw-bg-opacity: 1;
        background-color: rgb(249 250 251 / var(--tw-bg-opacity));
    }

    .bg-gray-700 {
        --tw-bg-opacity: 1;
        background-color: rgb(55 65 81 / var(--tw-bg-opacity));
    }

    .bg-gray-800 {
        --tw-bg-opacity: 1;
        background-color: rgb(31 41 55 / var(--tw-bg-opacity));
    }

    .bg-white {
        --tw-bg-opacity: 1;
        background-color: rgb(255 255 255 / var(--tw-bg-opacity));
    }

    .px-6 {
        padding-left: 1.5rem;
        padding-right: 1.5rem;
    }

    .py-3 {
        padding-top: 0.75rem;
        padding-bottom: 0.75rem;
    }

    .py-4 {
        padding-top: 1rem;
        padding-bottom: 1rem;
    }

    .text-left {
        text-align: left;
    }

    .text-right {
        text-align: right;
    }

    .text-sm {
        font-size: 0.875rem;
        line-height: 1.25rem;
    }

    .text-xs {
        font-size: 0.75rem;
        line-height: 1rem;
    }

    .font-medium {
        font-weight: 500;
    }

    .uppercase {
        text-transform: uppercase;
    }

    .text-gray-400 {
        --tw-text-opacity: 1;
        color: rgb(156 163 175 / var(--tw-text-opacity));
    }

    .text-gray-500 {
        --tw-text-opacity: 1;
        color: rgb(107 114 128 / var(--tw-text-opacity));
    }

    .text-gray-700 {
        --tw-text-opacity: 1;
        color: rgb(55 65 81 / var(--tw-text-opacity));
    }

    .text-gray-900 {
        --tw-text-opacity: 1;
        color: rgb(17 24 39 / var(--tw-text-opacity));
    }

    .text-white {
        --tw-text-opacity: 1;
        color: rgb(255 255 255 / var(--tw-text-opacity));
    }

    .rtl\:text-right:where([dir="rtl"],
    [dir="rtl"] *) {
        text-align: right;
    }

    @media (prefers-color-scheme: dark) {
        .dark\:border-gray-700 {
            --tw-border-opacity: 1;
            border-color: rgb(55 65 81 / var(--tw-border-opacity));
        }

        .dark\:bg-gray-700 {
            --tw-bg-opacity: 1;
            background-color: rgb(55 65 81 / var(--tw-bg-opacity));
        }

        .dark\:bg-gray-800 {
            --tw-bg-opacity: 1;
            background-color: rgb(31 41 55 / var(--tw-bg-opacity));
        }

        .dark\:text-gray-400 {
            --tw-text-opacity: 1;
            color: rgb(156 163 175 / var(--tw-text-opacity));
        }

        .dark\:text-white {
            --tw-text-opacity: 1;
            color: rgb(255 255 255 / var(--tw-text-opacity));
        }
    }
</style>
<div>
    <h2>
        فاکتور جدید
    </h2>
    <h3>
        <span>شماره فاکتور: </span>
        <span>{{ $order['order_number'] }}</span>
    </h3>
    <div class="relative overflow-x-auto">
        <table class="w-full text-sm text-left text-gray-500 rtl:text-right dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th scope="col" class="px-6 py-3">
                        نام محصول
                    </th>
                    <th scope="col" class="px-6 py-3">
                        تعداد
                    </th>
                    <th scope="col" class="px-6 py-3">
                        گروه
                    </th>
                    <th scope="col" class="px-6 py-3">
                        قیمت
                    </th>
                    <th scope="col" class="px-6 py-3">
                        قیمت با تخفیف
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order['items'] as $item)
                    @php
                        $itemInfo = json_decode($item['item_info']);
                    @endphp
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                        <th scope="row"
                            class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                            {{ $item['product']['name'] }}
                        </th>
                        <td class="px-6 py-4">
                            {{ $item['quantity'] }}
                        </td>
                        <td class="px-6 py-4">
                            {{ $item->product->category->name }}
                        </td>
                        <td class="px-6 py-4">
                            {{ $itemInfo->price->price }}
                        </td>
                        <td class="px-6 py-4">
                            {{ $itemInfo->price->discounted_price }}
                        </td>
                    </tr>
                @endforeach()
            </tbody>
        </table>
    </div>

    <table>
    </table>

    <div>
        <div>
            <h4>تلفن:</h4>
            <p>
                {{ $order['user']['phone'] }}
            </p>
        </div>
        <div>
            <h4>نام:</h4>
            <p>
                {{ $order['user']['name'] }}
            </p>
        </div>
    </div>

    <div>
        <h4>آدرس:</h4>
        <p>
            {{ $order['address']['state']['name'] }} -
            {{ $order['address']['city']['name'] }} -
            {{ $order['address']['address'] }}
        </p>
        @if ($order['address']['phone_number'])
            <p>
                <span>تلفن: </span>
                <span>{{ $order['address']['phone_number'] }}</span>
            </p>
        @endif
    </div>
</div>
