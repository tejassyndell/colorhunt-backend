import { Component, OnInit, ViewChild } from "@angular/core";
import { UserService } from "../../services/user.service";
import { Subject } from "rxjs";
import { RouterModule, Routes, Router } from "@angular/router";
import Swal from "sweetalert2";
import { DataTableDirective } from "angular-datatables";
import { environment } from "../../../environments/environment";
import { ToastrService } from "ngx-toastr";
import { NgxSpinnerService } from "ngx-spinner";
import { Title } from "@angular/platform-browser";

@Component({
  selector: "app-articlephotoslist",
  templateUrl: "./articlephotoslist.component.html",
  styleUrls: ["./articlephotoslist.component.scss"],
})
export class ArticlephotoslistComponent implements OnInit {
  articlephotolist: any = [];
  @ViewChild(DataTableDirective)
  dtElement: DataTableDirective;
  dtOptions: any;
  dtTrigger: Subject<any> = new Subject();
  BaseURL: any;
  isAdd: any;
  isDelete: any;
  isList: any;

  accessdenied: boolean = true;
  isEdit: any;
  showArticlePhotosLogs: boolean = false;
  ArticlePhotosLogsData: any;
  UserRole: any;
  constructor(
    private userService: UserService,
    public router: Router,
    private toastr: ToastrService,
    private spinner: NgxSpinnerService,
    private titleService: Title
  ) {
    this.titleService.setTitle("Article Photo List | Colorhunt");
  }

  ngOnInit() {
    let item = JSON.parse(localStorage.getItem("userdata"));
    this.UserRole = item[0].Role;
    let rolerights = JSON.parse(localStorage.getItem("roleright"));
    if (rolerights != "" && rolerights != null && rolerights != undefined) {
      this.rightscheck(rolerights, 1);
    } else {
      this.userService.getroleRights(item[0].Role).subscribe((res) => {
        this.rightscheck(res, 2);
      });
    }

    if (this.accessdenied == false) {
      setTimeout(() => this.spinner.show(), 25);
      this.dtOptions = {
        pagingType: "numbers",
        pageLength: 25,
      };
      this.isList = 1;
      this. getarticlephotoslist();
    } else {
      this.spinner.hide();
    }
  }

  public  getarticlephotoslist() {
    this.userService. GetArticlePhotosList().subscribe((res) => {
      const data = res;
      this.BaseURL = environment.UploadBaseURL;
      if (
        typeof this.dtElement !== "undefined" &&
        typeof this.dtElement.dtInstance !== "undefined"
      ) {
        this.dtElement.dtInstance.then((dtInstance: DataTables.Api) => {
          // Destroy the table first
          dtInstance.destroy();
          this.articlephotolist = data;
          // Call the dtTrigger to rerender again
          this.dtTrigger.next();
          this.spinner.hide();
        });
      } else {
        setTimeout(() => {
          this.articlephotolist = data;
          this.dtTrigger.next();
          this.spinner.hide();
        }, 100);
      }

      // this.dtTrigger.next();
    });
  }
  rightscheck(data, no) {
    let item = JSON.parse(localStorage.getItem("userdata"));
    if (no == 1) {
      var Count = Object.keys(data).length;
      for (let i = 0; i < Count; i++) {
        if (data[i].PageId == 18) {
          if (data[i].ListRights == 1) {
            this.accessdenied = false;
          } else {
            this.accessdenied = true;
          }
          this.isList = data[i].ListRights;
          this.isAdd = data[i].AddRights;
          this.isEdit = data[i].EditRights;
          this.isDelete = data[i].DeleteRights;
          break;
        } else {
          this.accessdenied = true;
        }
      }
    } else {
      for (let i = 0; i < data.length; i++) {
        if (data[i].PageId == 18) {
          if (data[i].ListRights == 1) {
            this.accessdenied = false;
          } else {
            this.accessdenied = true;
          }
          this.isList = data[i].ListRights;
          this.isAdd = data[i].AddRights;
          this.isEdit = data[i].EditRights;
          this.isDelete = data[i].DeleteRights;
          break;
        } else {
          this.accessdenied = true;
        }
      }
    }
  }

  ngOnDestroy(): void {
    // Do not forget to unsubscribe the event
    this.dtTrigger.unsubscribe();
  }
  public edit(id) {
    this.router.navigate(['articlephotos', { id: id }])
  }

  public delete(id) {
     console.log("id", id);
    let item = JSON.parse(localStorage.getItem("userdata"));
    Swal.fire({
      title: "Are you sure?",
      text: "You will not be able to recover this data!",
      type: "warning",
      showCancelButton: true,
      confirmButtonText: "Yes, delete it!",
      cancelButtonText: "No, keep it",
    }).then((result) => {
      if (result.value == true) {
        this.userService.DeleteArticlePhoto(id, item[0].Id).subscribe((res) => {
          console.log('test11',res);
          this. getarticlephotoslist();
          this.success(res);
        });
      } else {
      }
    });
  }
  public viewlogs(id) {
    this.showArticlePhotosLogs = true;
    this.userService.articlephotoslogs(id).subscribe((res) => {
      this.ArticlePhotosLogsData = res;
    });
  }
  goBack() {
    this.showArticlePhotosLogs = false;
  }
  success(data) {
    if (data.id != "") {
      this.toastr.success("Success", "Image Deleted Successfully");
    } else {
      this.toastr.error("Failed", "Please try agin later");
    }
  }
}
