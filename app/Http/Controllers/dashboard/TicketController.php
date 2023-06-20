<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        try {

            $tickets = Ticket::paginate(5);

            return response()->success($tickets, 200);

        } catch (\Exception $e) {


            return throwException('TicketController/create', $e);
        }

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {

        try {

            $ticket = Ticket::create([
                'subject' => $request->subject,
                'product_category' => $request->product_category,
                'request_details' => $request->request_details,
                'file' => $request['file']->getClientOriginalName()
            ]);


            if ($request->hasFile('file')) {


                $fileName = $request['file']->getClientOriginalName();


                $request['file']->move(public_path('uploads/user_id_' . $request->user()->id), $fileName);
            }

            $ticket->save();

            return response()->success($ticket, 201);
        } catch (\Exception $e) {


            return throwException('TicketController/create', $e);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}