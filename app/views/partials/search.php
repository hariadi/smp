<div class="input-group col-xs-12 <?php if ($messages) echo "has-warning"; ?>">
    <input type="text" class="form-control" placeholder="Search" name="term" id="term" value="<?php echo admin_search_term(); ?>">
    <div class="input-group-btn">
        <button class="btn btn-default" type="submit"><i class="glyphicon glyphicon-search"></i></button>
    </div>
</div>
