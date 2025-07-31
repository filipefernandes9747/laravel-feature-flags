<nav>
    <ul>
        <li><a href="{{ route('feature-flags.index') }}"
                class="{{ request()->routeIs('feature-flags.index') ? 'active' : '' }}">Flags</a></li>
        <li><a href="{{ route('feature-flags.history') }}"
                class="{{ request()->routeIs('feature-flags.history') ? 'active' : '' }}">History</a></li>
    </ul>
</nav>
