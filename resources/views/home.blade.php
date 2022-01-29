@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">本棚</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <div class="text-center">
                        {{ $msg }}
                    </div>
                    
                    <div>
                        <table class="table mt-3 mb-3" id="bookshelf">
                            @foreach($books as $book)
                            <?php
                                $percent = round(($book->reading_page / $book->full_page) * 100);
                            ?>

                            <tbody>
                                <tr>
                                    <td style="height: 1em; background-color: #dcdcdc;" colspan="2">
                                        {{ $book->title }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 224px;" rowspan="2">
                                        <img src="{{ $book->cover_url }}" style="width: 200px; border: 1px solid;">
                                    </td>
                                    <td class="align-middle text-center" style="width: 460px">
                                        <form action="{{ url('/home') }}" method="POST">
                                            @csrf
                                            <input type="hidden" id="jobtype" name="jobtype" value="update">
                                            <input type="hidden" id="book_id" name="book_id" value="{{ $book->id }}">
                                            <input type="range" style="height: 1rem; width: 15rem;" min="0" max="{{ $book->full_page }}" step="1" value="{{ $book->reading_page }}" id="{{ 'slider'.$book->id }}" name="progress_range">
                                            (<span id="{{ 'percent'.$book->id }}" style="display: inline-block; width: 26px;">{{ $percent }}</span>%)
                                            <div class="mt-1">
                                                <input type="number" min="0" max="{{ $book->full_page }}" step="1" value="{{ $book->reading_page }}" id="{{ 'num'.$book->id }}" name="progress_num">
                                                / {{ $book->full_page }} 
                                            </div>
                                            <div class="mt-5 mb-0">
                                                <a href="#" class="updatebtn">進捗を更新</a>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                                <tr class="subbutton-wrapper">
                                    <td class="text-right align-bottom" style="height: 1rem">
                                        <form action="{{ url('/home') }}" method="POST">
                                            @csrf
                                            <input type="hidden" id="book_id" name="book_id" value="{{ $book->id }}">
                                            <input type="hidden" id="jobtype" name="jobtype" value="delete">
                                            <button type="submit">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            </tbody>
                            @endforeach
                        </table>

                        <script>
                            $(function() {
                                $('#bookshelf input[type="number"]').on('input', function() {
                                    if (Number($(this).val()) > Number($(this).attr('max'))) $(this).val($(this).attr('max'));
                                    var id = $(this).attr('id').substr(3);
                                    $('#slider' + id).val(($(this).val().length == 0 ? 0 : $(this).val()));
                                    var percent = Math.floor(($(this).val() / $(this).attr('max')) * Math.pow(10, 2));
                                    $('#percent' + id).html(percent);
                                });

                                $('#bookshelf input[type="range"]').on('input', function() {
                                    var id = $(this).attr('id').substr(6);
                                    $('#num' + id).val($(this).val());
                                    var percent = Math.floor(($(this).val() / $(this).attr('max')) * Math.pow(10, 2));
                                    $('#percent' + id).html(percent);
                                });
                            });
                        </script>
                    </div>

                </div>
            </div>


            <div class="card mt-3">
                <div class="card-header">書籍登録</div>

                <div class="card-body">
                    <form action="{{ url('/home') }}" method="POST">
                        @csrf
                        <input type="hidden" id="title" name="title">
                        <input type="hidden" id="cover" name="cover">
                        <input type="hidden" id="jobtype" name="jobtype" value="add">
                        <table style="margin: auto;">
                            <tr>
                                <td class="text-right p-1">ISBN</td>
                                <td class="text-left p-1">
                                    <input type="text" id="isbn" name="isbn" placeholder="例)978-4-949999-12-0" style="width: 12rem;" autocomplete="off" required>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-right p-1">ページ数</td>
                                <td class="text-left p-1">
                                    <input type="number" min="1" max="2147483647" step="1" style="width: 12rem;" id="num_of_pages" name="num_of_pages" required>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" class="text-center p-1">
                                    <input type="submit" name="addbtn" id="addbtn" value="登録" disabled>
                                </td>
                            </tr>
                        </table>
                    </form>

                    <script>
                    $(function() {
                        $('#isbn').on('input', function() {
                            if ($('#isbn').val().length == 0) {
                                $('#preview').html("");
                            } else {
                                var json = $.getJSON('https://api.openbd.jp/v1/get?isbn=' + $('#isbn').val(), function(json) {
                                    if (json[0] != null) {
                                        /*--- Create preview table ---*/
                                        const preview_table = $('<table><tbody>');
                                        preview_table.addClass('m-auto');
                                        preview_table.addClass('lead');

                                        const title_preview_row = $('<tr></tr>');
                                        const title_preview = $('<td></td>');
                                        title_preview.attr('id', 'title_preview');
                                        title_preview.attr('colspan', '2');
                                        title_preview.addClass('p-2');
                                        title_preview.css('background-color', '#dcdcdc');
                                        title_preview.appendTo(title_preview_row);
                                        title_preview_row.appendTo(preview_table);

                                        const book_info_row = $('<tr></tr>');                                    
                                        const cover_preview_col = $('<td></td>');
                                        cover_preview_col.addClass('p-2');
                                        cover_preview_col.css('width', '224px');                                    
                                        const cover_preview = $('<img>');
                                        cover_preview.attr('id', 'cover_preview');
                                        cover_preview.css('width', '200');
                                        cover_preview.css('border', '1px solid');
                                        cover_preview.appendTo(cover_preview_col);
                                        cover_preview_col.appendTo(book_info_row);

                                        const author_preview_col = $('<td></td>');
                                        author_preview_col.addClass('p-2');
                                        author_preview_col.addClass('align-middle');
                                        const author_preview = $('<p></p>');
                                        author_preview.attr('id', 'author_preview');
                                        author_preview.appendTo(author_preview_col);
                                        const publisher_preview = $('<p></p>');
                                        publisher_preview.attr('id', 'publisher_preview');
                                        publisher_preview.appendTo(author_preview_col);
                                        author_preview_col.appendTo(book_info_row);
                                        book_info_row.appendTo(preview_table);

                                        $('</tbody></table>').appendTo(preview_table);
                                        $('#preview').html(preview_table);
                                        /*-------*/

                                        /*--- Set value ---*/
                                        $('#title_preview').html(json[0]["summary"]["title"]);
                                        $('#title').val(json[0]["summary"]["title"]);
                                        const cover_url = (json[0]["summary"]["cover"].length == 0 ? './templates/noimage.png' : json[0]["summary"]["cover"]);
                                        $('#cover_preview').attr('src', cover_url);
                                        $('#cover').val(cover_url);
                                        $('#author_preview').html(json[0]["summary"]["author"]);
                                        $('#publisher_preview').html(json[0]["summary"]["publisher"]);
                                        /*------*/

                                        /*--- Activate submit button ---*/
                                        $('#addbtn').prop('disabled', false);
                                        /*------*/
                                    } else {
                                        const msg = $('<p>');
                                        msg.addClass('lead');
                                        msg.addClass('text-center');
                                        msg.append("Book's information does not found");
                                        $('</p>').appendTo(msg);
                                        $('#preview').html(msg);

                                        /*--- Disable submit button ---*/
                                        $('#addbtn').prop('disabled', true);
                                        /*------*/
                                    }
                                });
                            }
                        });
                    });
                    </script>

                    <div id="preview" class="mt-4 mb-4">
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>
@endsection
