@extends('backend.layouts.app')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row align-items-center mb-3">
            <div class="col-12">
                <div class="page-title-box">
                    <h4 class="mb-0 fs-18">Margin Estimator</h4>
                </div>
            </div>
        </div>
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form id="margin-form" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Price</label>
                        <input type="number" step="0.01" name="price" class="form-control" value="100">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Commission %</label>
                        <input type="number" step="0.01" name="commission_percent" class="form-control" value="10">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">PG Fee %</label>
                        <input type="number" step="0.01" name="pg_percent" class="form-control" value="2">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Discount</label>
                        <input type="number" step="0.01" name="discount" class="form-control" value="0">
                    </div>
                    <div class="col-12">
                        <button class="btn btn-primary" type="button" id="calc-btn">Calculate</button>
                    </div>
                </form>
                <div class="mt-3" id="margin-result" style="display:none;">
                    <div class="alert alert-info mb-0">
                        <div>Net: <span id="net-val"></span></div>
                        <div>Margin %: <span id="margin-val"></span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
document.getElementById('calc-btn').addEventListener('click', async () => {
  const form = document.getElementById('margin-form');
  const fd = new FormData(form);
  const resp = await fetch('{{ route('vendor.margin.calculate') }}', {
    method: 'POST',
    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
    body: fd
  });
  const data = await resp.json();
  if (data?.status) {
    document.getElementById('net-val').innerText = Number(data.data.net).toFixed(2);
    document.getElementById('margin-val').innerText = Number(data.data.margin_percent).toFixed(2) + '%';
    document.getElementById('margin-result').style.display = 'block';
  }
});
</script>
@endsection
