@if (count($topics))
    <ul class="media-list">
        @foreach($topics as $topic)
            <li class="media">
                <div class="media-left">
                    <a href="{{ route('users.show', [$topic->user_id]) }}">
                        <img class="media-object img-thumbnail" style="width: 52px; height: 52px;" src="{{ $topic->user->avatar }}" title="{{ $topic->user->name }}">
                    </a>
                </div>

                <div class="media-body">
<span class="timeago" title="最后活跃于">
     {{ if_query('order','recent') ? $topic->created_at->diffForHumans().'发布' : $topic->updated_at->diffForHumans().'回复' }}
</span>
                    <div class="media-heading">
                        <a href="{{ $topic->link() }}">
                            {{ $topic->title }}
                        </a>
                        <a class="pull-right" href="{{ $topic->link() }}">
                            <span class="badge">{{ $topic->reply_count }} </span>
                        </a>
                    </div>

                    <div class="media-body meta">

                        <a href="{{ route('categories.show', $topic->category->id) }}" title="{{ $topic->category->name }}">
                            <span class="glyphicon glyphicon-folder-open" aria-hidden="true"></span>
                            {{ $topic->category->name }}
                        </a>

                        <span> • </span>
                        <a href="{{ route('users.show', [$topic->user_id]) }}"  title="{{ $topic->user->name }}">
                             <span class="glyphicon glyphicon-user" aria-hidden="true"></span>
                            {{ $topic->user->name }}
                        </a>
                        <span> • </span>
                        <span class="glyphicon glyphicon-time" aria-hidden="true"></span>
                        <span class="timeago" title="最后活跃于">{{ $topic->updated_at->diffForHumans() }}</span>
                    </div>
                </div>

            </li>

            @if ( ! $loop->last)
                <hr>
            @endif
        @endforeach
@else
   <div class="empty-block">暂无数据 ~_~ </div>
    </ul>
@endif