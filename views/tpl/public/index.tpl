
<div class="row">
    <div class="col-md-3">
        <h3>Popular</h3>

        <ul>
        <e:section name="popular">
            <li><a href="/tag/~popular.tag~">~popular.tag~ (~popular.count~)</li>
        </e:section>
        </ul>

    </div>

    <div class="col-md-9">
        <h3>Photos</h3>

        <!-- Tried to find Bootstrap gallery examples, but screen reader kept freezing right now, so just modify as needed -->
        <ul>
        <e:section name="photos">
        <li>
            <a href="~photos.url~" title="~photos.title~">
                <img src="~photos.thumb_src~" alt="~photos.title~" width="~photos.width~" height="~photos.height~" border="0"><br />
                <span>~photos.title~</span><br />
                ~photos.tags~
            </a>
        </li>
        </e:section>

    </ul>

        <e:pagination href="route" rows_per_page="~scraper_photos_per_page~" total="~total_photos~" page="~page~">~total_photos~" 

    </div>

</div>


