<x-filament-widgets::widget>
  <x-alert type="{{ $licenseValid ? 'success' : 'danger' }}">
    @slot('title')
      {{ $licenseValid ? 'License Valid' : 'License Invalid' }}
    @endslot
    @if ($licenseValid)
      <p>You have access to all the releases.</p>
    @else
      @if ($licenseValidTo)
        <p>You won't be able to access the latest releases after {{ $licenseValidTo->format('Y-m-d') }}.</p>
      @else
        <p>You won't be able to access the latest releases.</p>
      @endif
    @endif
  </x-alert>
</x-filament-widgets::widget>
