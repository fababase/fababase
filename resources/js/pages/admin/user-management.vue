<template>
	<div>
		<h2>User management</h2>
		<p>Manage user roles and permissions here.</p>

		<!-- Spinner -->
		<div
			v-if="!users.length"
			class="d-flex justify-content-center"
			style="height: 80px;">
			<div
				class="spinner-border"
				role="status">
				<span class="sr-only">Loading&hellip;</span>
			</div>
		</div>

		<!-- Users table -->
		<table
			v-else
			class="table table-hover">
			<thead>
				<tr>
					<th scope="col" rowspan="2">Name</th>
					<th scope="col" rowspan="2">Email</th>
					<th v-bind:colspan="rolesColSpan">Roles</th>
				</tr>
				<tr>
					<th
						scope="col"
						v-for="(role, i) in roles"
						v-bind:key="i">
						{{ roleName[role] }}
					</th>
				</tr>
			</thead>
			<tbody>
				<tr
					v-for="(user, i) in users"
					v-bind:key="i">
					<td>{{ user.name }}</td>
					<td>{{ user.email }}</td>
					<td
						class="text-center"
						v-for="(role, j) in roles"
						v-bind:key="j">
						<div class="custom-control custom-switch">
							<input
								type="checkbox"
								class="custom-control-input"
								v-model="user[role]"
								v-on:input="onToggleInput(user, role, $event)"
								v-bind:id="getToggleId(role, i)" />
							<label
								class="custom-control-label"
								v-bind:for="getToggleId(role, i)"
								v-bind:title="getToggleTitle(user, role)"></label>
						</div>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
</template>

<script>
import axios from 'axios';
import { mapGetters } from 'vuex';

export default {
	scrollToTop: false,

	data: function() {
		return {
			users: [],
			roles: [
				'is_norfab_user',
				'is_profaba_user',
			],
			roleName: {
				'is_norfab_user': 'NorFab',
				'is_profaba_user': 'ProFaba',
			}
		};
	},

	async mounted() {
		const { data } = await axios.get(`/api/users`);
		this.users = data;
	},

	computed: {
		rolesColSpan() {
			return this.roles.length;
		},

	},

	methods: {
		onToggleInput(user, role, e) {
			this.updateRoleByUser(user, role, e.target);
		},

		getToggleId(...args) {
			return args.join('_');
		},

		getToggleTitle(user, role) {
			return `${user[role] ? 'Remove' : 'Grant'} users to have permissions granted for the group ${this.roleName[role]}`;
		},

		async updateRoleByUser({ email }, role, checkboxElement) {
			checkboxElement.disabled = true;
			await axios.patch(`/api/users/update-role-by-user`, {
				email,
				role,
				roleFlag: checkboxElement.checked,
			});

			checkboxElement.disabled = false;
		}
	},
}
</script>

<style lang="scss">
$label-color: #20c997;

.custom-switch .custom-control-input {
	& ~ .custom-control-label {
		cursor: pointer;
	}

	&:disabled ~ .custom-control-label {
		cursor: wait;
	}

	&:checked ~ .custom-control-label::before {
		border-color: $label-color;
		background-color: $label-color;
	}

	&:disabled:checked ~ .custom-control-label::before {
		border-color: rgba($label-color, 0.5);
		background-color: rgba($label-color, 0.5);
	}
}
</style>
