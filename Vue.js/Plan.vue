<template>
    <div class="content pt-5">
        <div class="row justify-content-center">
            <div class="col-12 p-5">
                <div class="card text-center border-top-secondary">
                    <div class="card-body">
                        <h4 class="card-title">Personal</h4>
                        <p class="card-text">Includes Family</p>
                        
                        <h3>$ Cost</h3>
                        <ul class="text-left list-unstyled m-5">
                            <li>Lorem Ipsum</li>
                            <li>Lorem Ipsum</li>
                            <li>Lorem Ipsum</li>
                            <li>Lorem Ipsum</li>
                            <li>Lorem Ipsum</li>
                            <li>Lorem Ipsum</li>
                        </ul>

                        <button class="btn btn-outline-success w-100" @click="showMsg(1)">Choose Personal</button>
                    </div>
                </div>
            </div>
            <div class="col-12 p-5">
                <div class="card text-center border-top-success">
                    <div class="card-body">
                        <h4 class="card-title">Organization</h4>
                        
                        <h3>$ Cost</h3>
                        <ul class="text-left list-unstyled m-5">
                            <li>Lorem Ipsum</li>
                            <li>Lorem Ipsum</li>
                            <li>Lorem Ipsum</li>
                            <li>Lorem Ipsum</li>
                            <li>Lorem Ipsum</li>
                            <li>Lorem Ipsum</li>
                        </ul>

                        <button class="btn btn-success w-100" @click="showMsg(2)">Choose Personal</button>
                    </div>
                </div>
            </div>
            <div class="col-12 p-5">
                <div class="card text-center border-top-purple">
                    <div class="card-body">
                        <h4 class="card-title">Enterprise</h4>
                        
                        <h3>$ Cost</h3>
                        <ul class="text-left list-unstyled m-5">
                            <li>Lorem Ipsum</li>
                            <li>Lorem Ipsum</li>
                            <li>Lorem Ipsum</li>
                            <li>Lorem Ipsum</li>
                            <li>Lorem Ipsum</li>
                            <li>Lorem Ipsum</li>
                        </ul>

                        <button class="btn btn-purple w-100 text-white" @click="showMsg(3)">Choose Personal</button>
                    </div>
                </div>

            </div>
            
            <my-footer></my-footer>
        </div>
    </div>
</template>

<script>
import SubscriptionManagement from './user/SubscriptionManagement.vue';
export default {
  components: { SubscriptionManagement },
    data(){
        return {
            activePlan:false,
            selectedPlan:''
        }
    },
    created(){
        this.$parent.changeTitle('Pricing');
    },
    methods:{
        showMsg(planId) {
            this.selectedPlan = planId;
            this.activePlan = true;
            this.$router.push('/subscription?plan='+this.selectedPlan);
        },
    }
}
</script>