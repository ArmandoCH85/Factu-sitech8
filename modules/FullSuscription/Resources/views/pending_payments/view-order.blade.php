@extends('tenant.layouts.web')

@section('content')

    <full-suscription-pending-payments-view-order
        :order="{{ json_encode($order) }}"
        :company="{{ json_encode($company) }}"
    >
    </full-suscription-pending-payments-view-order>

@endsection
