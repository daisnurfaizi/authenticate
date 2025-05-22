<?php

namespace Ijp\Auth\Traits;

trait PaginateResolver
{
    /**
     * Resolve the pagination parameters from the request.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function resolvePagination($request)
    {

        $paginate = $request->input('paginate', false);
        $perPage = $request->input('perPage', 10);

        return [
            'paginate' =>  $paginate,
            'perPage' => (int) $perPage,
        ];
    }
}
