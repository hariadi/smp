<?php echo $header; ?>

<h1 class="page-header"><?php echo __('transfers.transfer'); ?></h1>

<?php echo $messages; ?>

<div class="row">
	<div class="col-lg-9">

		<form class="form-horizontal" role="search">
			<?php echo $search; ?>

			<div class="table-responsive">
				<table class="table table-hover">
					<thead>
						<tr>
							<th>#</th>
							<th><?php echo __('transfers.staff'); ?></th>
							<th><?php echo __('transfers.transfer_from'); ?></th>
							<th><?php echo __('transfers.transfer_to'); ?></th>
							<th class="hidden-xs"><?php echo __('transfers.request_by'); ?></th>
							<th class="hidden-xs"><?php echo __('transfers.transfer_at'); ?></th>
							<th class="hidden-xs"><?php echo __('transfers.confirm_at'); ?></th>
							<th class="hidden-xs"><?php echo __('transfers.confirm_by'); ?></th>
							<th><?php echo __('transfers.status'); ?></th>
							<th><?php echo __('transfers.action'); ?></th>
						</tr>
					</thead>
					<tbody>
					<?php if($transfers->count): ?>
						<?php foreach($transfers->results as $transfer): ?>
						<tr class="status draft">
							<td><?php echo $transfer->id; ?></td>
							<td>
								<a href="<?php echo Uri::to('admin/transfers/view/' . $transfer->id); ?>"><?php echo $transfer->staff; ?></a> -
								<a href="<?php echo Uri::to('admin/staffs/edit/' . $transfer->staff_id); ?>" data-toggle="tooltip" title="Kemaskini profil"><i class="glyphicon glyphicon-edit"></i></a>
							</td>
							<td class="text-uppercase"><?php echo $transfer->division_from_slug; ?></td>
							<td class="text-uppercase"><?php echo $transfer->division_to_slug; ?></td>
							<td class="hidden-xs">

								<a href="<?php echo Uri::to('admin/profile/' . $transfer->transfer_by); ?>"><?php echo $transfer->request_by; ?></a>


							</td>
							<td class="hidden-xs"><?php echo $transfer->transfered_at; ?></td>
							<td class="hidden-xs"><?php echo $transfer->confirmed_at; ?></td>
							<td class="hidden-xs"><?php echo $transfer->confirm_by; ?></td>
							<td>

								<span class="text-uppercase label label-<?php echo $labels[$transfer->status] ?>"><?php echo $statuses[$transfer->status] ?></span>
										</td>
							<td>

							<?php if ($transfer->transfer_by == $editor->id || (!$transfer->confirmed_at && in_array($transfer->division_from_id, $editor->roles)) ) : ?>
								<a class="btn btn-danger btn-xs" href="<?php echo Uri::to('admin/transfers/cancel/'. $transfer->id) ?>"><i class="glyphicon glyphicon-remove-sign"></i> Batal</a>
							<?php endif; ?>

							<?php if (!$transfer->confirmed_at && in_array($transfer->division_to_id, $editor->roles) ) : ?>
								<a class="btn btn-success btn-xs" href="<?php echo Uri::to('admin/transfers/confirm/'. $transfer->id) ?>"><i class="glyphicon glyphicon-ok-sign"></i> Sah</a>
							<?php endif; ?>

							</td>
						</tr>
					<?php endforeach; ?>
					<?php else: ?>
	                <tr>
	                  <td colspan="7"><?php echo __('transfers.no_reports'); ?></td>
	                </tr>
					<?php endif; ?>
				</tbody>
			</table>

			<?php if ($transfers->links()) : ?>
			<ul class="pagination">
				<?php echo $transfers->links(); ?>
			</ul>
			<?php endif; ?>

			</div>
		</form>
	</div>
	<div class="col col-lg-3">
		<nav class="list-transfer sidebar">

            <div class="panel panel-default">
                <div class="panel-heading"><?php echo __('transfers.status'); ?></div>
                <div class="list-group">
                <?php parse_str(Request::segments(), $segment);  ?>
                <a class="list-group-item <?php if(empty($segment)) echo 'active'; ?>" href="<?php echo Uri::to('admin/transfers'); ?>">Semua</a>
                <?php foreach ($statuses as $key => $status) : ?>
                <a class="list-group-item <?php if(in_array($key, array_values($segment))) echo 'active'; ?>" href="<?php echo Uri::to('admin/transfers/?status=' . $key); ?>"><?php echo $status; ?></a>
                 <?php  endforeach; ?>
                </div>
            </div>
		</nav>
	</div>
</div>

<?php echo $footer; ?>
