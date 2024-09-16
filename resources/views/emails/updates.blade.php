<x-mail::message>
  <x-mail::panel>
    Some of the packages you are using have new releases available. These were released between
    {{ $yesterday->format(config('app.date_time_format')) }} and {{ $now->format(config('app.date_time_format')) }}.
  </x-mail::panel>
  <x-mail::table>
    <table>
      <thead>
        <tr>
          <th>Package</th>
          <th>Version</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($packages as $packageVendoredName => $releases)
          @foreach ($releases as $release)
            <tr>
              @if ($loop->first)
                <td
                  rowspan="{{ count($releases) }}"
                  valign="top"
                >
                  <a
                    href="{{ route('filament.admin.resources.packages.edit', $release['package']->id) }}">{{ $packageVendoredName }}</a>
                </td>
              @endif
              <td>{{ $release['version'] }}</td>
            </tr>
          @endforeach
        @endforeach
      </tbody>
    </table>
  </x-mail::table>

  <x-mail::button :url="URL::to('/')">
    Visit {{ config('app.name') }}
  </x-mail::button>
</x-mail::message>
