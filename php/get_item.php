<div id="match-form">
    <h2>Match Items</h2>
    <form action="confirm_action.php" id="myform" method="POST">
        <label for="lost_item_id">Lost Item ID:</label>
        <select name="lost_item_id" id="lost_item_id" required>
            <!-- Options will be dynamically populated -->
        </select><br>

        <label for="found_item_id">Found Item ID:</label>
        <select name="found_item_id" id="found_item_id" required>
            <!-- Options will be dynamically populated -->
        </select><br>

        <button type="submit" name="action" value="match">Confirm Match</button>
        <button type="button" onclick="hideMatchForm()">Cancel</button>
    </form>
</div>