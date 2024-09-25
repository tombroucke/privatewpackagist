<div
  class="rounded-md {{ $backgroundColor }} p-4"
  role="alert"
>
  @if (isset($title))
    <strong>{{ $title }}</strong>
  @endif
  {{ $slot }}
</div>
