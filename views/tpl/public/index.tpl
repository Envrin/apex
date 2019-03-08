

<div class="row">
    <div class="col-md-6">
        <h3>Latest Blocks</h3>

        <table class="table table-bordered table-striped table-hover">
        <thead><tr>
            <th>Height</th>
            <th>Age</th>
            <th>Transactions</th>
            <th>Total Sent</th>
            <th>Size (kb)</th>
            <th>Weight</th>
        </tr></thead>

        <tbody>
        <e:section name="blocks">
        <tr>
            <td><a href="/block/~blocks.height~">~blocks.height~</a></td>
            <td>~blocks.age~</td>
            <td>~blocks.transactions~</td>
            <td>~blocks.total_sent~</td>
            <td>~blocks.size~</td>
            <td>~blocks.weight~</td>
        </tr>
        </e:section>
        </tbody></table><br />

    </div>

    <div class="col-md-6">
        <h3>Network</h3>

        <table class="table table-bordered table-striped table-hover">
        <tr>
            <td><b>Currency:</b></td>
            <td>Bitcoin (BTC)</td>
        </tr><tr>
            <td><b>Current Rate:</b></td>
            <td>~current_rate~</td>
        </tr><tr>
            <td><b>24 Hour Volume:</b></td>
            <td>~lastday_volume~</td>
        </tr><tr>
            <td><b>Market Cap:</b></td>
            <td>~market_cap~</td>
        </tr><tr>
            <td><b>Total Supply:</b></td>
            <td>~total_supply~</td>
        </tr><tr>
            <td><b>24 Hour Change (%):</b></td>
            <td>~percent_change_24h~</td>
        </tr><tr>
            <td><b>7 Day Change (%):</b></td>
            <td>~percent_change_7d~</td>
        </tr></table><br />

    </div>

</div>

<div class="row">
    <div class="col-md-12">

        <h3>Latest Transactions</h3>

        <table class="table table-bordered table-striped table-hover">
        <thead><tr>
            <th>TxID</th>
            <th>Age</th>
            <th>Amount Sent</th>
            <th>Confirmations</th>
        </tr></thead>

        <tbody>
        <e:section name="tx">
        <tr>
            <td><a href="/tx/~tx.txid~">~tx.txid~</a></td>
        <td>~tx.age~</td>
            <td>~tx.amount_sent~</td>
            <td>~tx.confirmations~</td>
        </tr>
        </e:section>
        </tbody></table><br />

    </div>
</div>





