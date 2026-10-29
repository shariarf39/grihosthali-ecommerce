<form method="POST" action="{{ $action }}" data-confirm="{{ $confirm ?? 'Are you sure you want to delete this?' }}">
    @csrf @method('DELETE')
    <button class="btn btn-sm btn-danger btn-icon" title="Delete"><i class="fa-regular fa-trash-can"></i></button>
</form>
